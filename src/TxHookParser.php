<?php declare(strict_types=1);

namespace XRPLWin\XRPLHookParser;

/**
 * Transaction Hook Parser
 * Extract list of HookHash
 */
class TxHookParser
{
  private readonly \stdClass $tx;
  private readonly \stdClass $meta;

  private array $map_full = [];
  private array $map_hash_accounts = [];
  private array $map_account_hash = [];
  private array $map_hashes = [];
  private array $map_typeevent_hashes = [];
  //private array $result = [];

  public function __construct(\stdClass $tx, array $options = [])
  {
    //Normalize account output
    if(isset($tx->Account) && $tx->Account === '') {
      $tx->Account = 'rrrrrrrrrrrrrrrrrrrrrhoLvTp'; //Account ZERO, eg NULL
    }

    $this->tx = $tx;
    $this->meta = isset($this->tx->meta) ? $this->tx->meta : $this->tx->metaData;

    $this->extractHooksFromEmitDetails();
    $this->extractHooksFromMeta();
    $this->extractHooksFromContext();
  }

  private function extractHooksFromEmitDetails(): void
  {
    if(!isset($this->tx->EmitDetails))
      return;
    if(isset($this->tx->EmitDetails->EmitHookHash)) {
      $this->addHook(
        $this->tx->EmitDetails->EmitHookHash,
        $this->tx->Account,
        'EmitDetails',
        ($this->tx->TransactionType == 'EmitFailure' ? 'emitfail':'emitsuccess'),
      );
    }
    //dd($this->tx->EmitDetails);
  }

  private function extractHooksFromMeta(): void
  {
    if(isset($this->meta->AffectedNodes)) {
      foreach($this->meta->AffectedNodes as $AffectedNode) {
        if(isset($AffectedNode->CreatedNode)) { //Created
          if(isset($AffectedNode->CreatedNode->LedgerEntryType)) {
            switch($AffectedNode->CreatedNode->LedgerEntryType) {
              case 'HookDefinition':
                $this->addHook(
                  $AffectedNode->CreatedNode->NewFields->HookHash,
                  null,
                  'HookDefinition',
                  'created'
                );
                break;
              case 'Hook':
                foreach($AffectedNode->CreatedNode->NewFields->Hooks as $hook) {
                  if(isset($hook->Hook->HookHash)) {
                    $this->addHook(
                      $hook->Hook->HookHash,
                      $AffectedNode->CreatedNode->NewFields->Account, //affected account
                      'Hook',
                      'installed'
                    );
                  }
                }
                break;
              case 'HookState':
                //todo ?
                break;
            }
          }
        }
        if(isset($AffectedNode->ModifiedNode)) { //Modified (see if changes removed or added hooks)
          if(isset($AffectedNode->ModifiedNode->LedgerEntryType)) {
            switch($AffectedNode->ModifiedNode->LedgerEntryType) {
              case 'Hook':
                $parsed = $this->hookChangesFromModifiedHookNode(
                  (isset($AffectedNode->ModifiedNode->PreviousFields) && $AffectedNode->ModifiedNode->PreviousFields)?$AffectedNode->ModifiedNode->PreviousFields->Hooks:null,
                  (isset($AffectedNode->ModifiedNode->FinalFields) && $AffectedNode->ModifiedNode->FinalFields)?$AffectedNode->ModifiedNode->FinalFields->Hooks:null
                );
                if(count($parsed['added'])) {
                  foreach($parsed['added'] as $h => $hv) {
                    $this->addHook(
                      $h,
                      $AffectedNode->ModifiedNode->FinalFields->Account, //affected account
                      'Hook',
                      'installed'
                    );
                  }
                }

                if(count($parsed['removed'])) {
                  foreach($parsed['removed'] as $h => $hv) {
                    $this->addHook(
                      $h,
                      $AffectedNode->ModifiedNode->FinalFields->Account, //affected account
                      'Hook',
                      'uninstalled'
                    );
                  }
                }

                if(count($parsed['modified'])) {
                  foreach($parsed['modified'] as $h => $hv) {
                    $this->addHook(
                      $h,
                      $AffectedNode->ModifiedNode->FinalFields->Account, //affected account
                      'Hook',
                      'modified'
                    );
                  }
                }
                
                //dd($parsed);
                /*foreach($AffectedNode->ModifiedNode->FinalFields->Hooks as $hook) {
                  if(isset($hook->Hook->HookHash)) {
                    $this->addHook(
                      $hook->Hook->HookHash,
                      $AffectedNode->ModifiedNode->FinalFields->Account, //affected account
                      'Hook',
                      'updated'
                    );
                  }
                }*/
                break;
            }
          }
        }

        if(isset($AffectedNode->DeletedNode)) { //Deleted
          if(isset($AffectedNode->DeletedNode->LedgerEntryType)) {
            switch($AffectedNode->DeletedNode->LedgerEntryType) {
              case 'HookDefinition':
                $this->addHook(
                  $AffectedNode->DeletedNode->FinalFields->HookHash,
                  null,
                  'HookDefinition',
                  'destroyed'
                );
                break;
              case 'Hook':
                foreach($AffectedNode->DeletedNode->FinalFields->Hooks as $hook) {
                  if(isset($hook->Hook->HookHash)) {
                    
                    $this->addHook(
                      $hook->Hook->HookHash,
                      $AffectedNode->DeletedNode->FinalFields->Account, //affected account
                      'Hook',
                      'uninstalled'
                    );
                  }
                }
                break;
            }
          }
        }
      }
    }

    if(isset($this->meta->HookExecutions)) {
      foreach($this->meta->HookExecutions as $HookExecution) {
        $this->addHook(
          $HookExecution->HookExecution->HookHash,
          $HookExecution->HookExecution->HookAccount, //affected account
          'HookExecution',
          'target'
        );
      }
    }
    //dd($this->meta,$this->map_full,$this->map_hash_accounts,$this->map_account_hash,$this->map_hashes);
  }

  private function normalizeHookNode(\stdClass $node): array
  {
    $n = (array)$node;
    //if(!isset($n['Flags'])) $n['Flags'] = 0;
    \ksort($n);
    return $n;
  }

  private function hookChangesFromModifiedHookNode(?array $prev, ?array $final): array
  {
    $prev = $prev === null?[]:$prev;
    $final = $final === null?[]:$final;

    $r = ['added' => [], 'removed' => [], 'unmodified' => [], 'modified' => []];
    $tracker = [];

    foreach($prev as $p) {
      if(!isset($p->Hook->HookHash)) continue;
      $h = $p->Hook->HookHash;
      $contents = $this->normalizeHookNode($p->Hook);
      $tracker[$h] = ['state' => 1, 'hsh' => [\json_encode($contents)]];
    }
    
    foreach($final as $p) {
      if(!isset($p->Hook->HookHash)) continue;
      $h = $p->Hook->HookHash;
      $contents = $this->normalizeHookNode($p->Hook);
      if(!isset($tracker[$h])) {
        $tracker[$h] = ['state' => 0, 'hsh' => [\json_encode($contents)]];
      } else {
        $tracker[$h]['hsh'][] = \json_encode($contents);
        $tracker[$h]['state']++;
      }
    }

    foreach($tracker as $h => $data) {
      $state = $data['state'];
      if($state === 0) {
        //hook added
        $r['added'][$h] = true;
      } else if($state === 1) {
        //hook removed
        $r['removed'][$h] = true;
      } else if($state === 2) {
        //hook kept
        if(count($data['hsh']) > 1 && $data['hsh'][0] != $data['hsh'][1]) {
          $r['modified'][$h] = true;
        } else {
          $r['unmodified'][$h] = true;
        }
      }
    }
    return $r;
  }

  private function extractHooksFromContext(): void
  {
    # Invoked hook (https://docs.xahau.network/features/transaction-types/invoke)
    # This case is not directly displayed in metadata, Account which is executing transaction is hook invoker
    if($this->tx->TransactionType == 'Invoke') {
      foreach($this->map_typeevent_hashes as $k => $v) {
        if($k == 'HookExecution_target') {
          foreach($v as $h) {
            $this->addHook(
              $h,
              $this->tx->Account, //affected account
              'HookExecution',
              'invoker'
            );
          }
        }
      }
    }
  }

  private function addHook(string $hookHash, ?string $account, string $fromType, string $event): void
  {
    $account = $account !== null ? $account:'NULL';
    $this->map_full[$account][$fromType][$event][] = $hookHash;

    $this->map_hashes[$hookHash] = true;
    $this->map_typeevent_hashes[$fromType.'_'.$event][] = $hookHash;

    if(!isset($this->map_hash_accounts[$hookHash]))
      $this->map_hash_accounts[$hookHash] = [];

    if($account !== 'NULL') {
      $this->map_hash_accounts[$hookHash][] = $account;

      if(!isset($this->map_account_hash[$account]))
        $this->map_account_hash[$account] = [];
      $this->map_account_hash[$account][] = $hookHash;
    }
  }

  # PUBLIC METHODS:

  /**
   * Returns full list of affected hooks.
   * @return array
   */
  public function hooks(): array
  {
    return \array_keys($this->map_hashes);
  }

  /**
   * List of hook affected accounts.
   * @return array
   */
  public function accounts(): array
  {
    $data = $this->map_full;
    unset($data['NULL']);
    return \array_keys($data);
  }

  /**
   * Get list of hooks of affected provided account.
   * @return array
   */
  public function accountHooks(string $address): array
  {
    if(!isset($this->map_account_hash[$address]))
      return [];
    return \array_values(\array_unique($this->map_account_hash[$address]));
  }

  /**
   * Get list of accounts that are affected by provided hook.
   * @return array
   */
  public function hookAccounts(string $hookHash): array
  {
    if(!isset($this->map_hash_accounts[$hookHash]))
      return [];
    return \array_values(\array_unique($this->map_hash_accounts[$hookHash]));
  }

  /**
   * Get list of newly created hooks (new HookDefinition).
   */
  public function createdHooks(): array
  {
    if(!isset($this->map_typeevent_hashes['HookDefinition_created']))
      return [];
    return \array_values(\array_unique($this->map_typeevent_hashes['HookDefinition_created']));
  }

  /**
   * Check if specific hook is created
   */
  public function isHookCreated(string $hookhash): bool
  {
    return (\in_array($hookhash, $this->createdHooks()));
  }

  /**
   * Get list of destroyed hooks (deleted HookDefinition).
   */
  public function destroyedHooks(): array
  {
    if(!isset($this->map_typeevent_hashes['HookDefinition_destroyed']))
      return [];
    return \array_values(\array_unique($this->map_typeevent_hashes['HookDefinition_destroyed']));
  }

  /**
   * Check if specific hook is created
   */
  public function isHookDestroyed(string $hookhash): bool
  {
    return (\in_array($hookhash, $this->destroyedHooks()));
  }

  
  /**
   * Get list of installed hooks to account in this transaction.
   */
  public function installedHooks(): array
  {
    if(!isset($this->map_typeevent_hashes['Hook_installed']))
      return [];
    return \array_values(\array_unique($this->map_typeevent_hashes['Hook_installed']));
  }

  /**
   * Get list of uninstalled hooks from account in this transaction.
   */
  public function uninstalledHooks(): array
  {
    if(!isset($this->map_typeevent_hashes['Hook_uninstalled']))
      return [];
    return \array_values(\array_unique($this->map_typeevent_hashes['Hook_uninstalled']));
  }

  public function modifiedHooks(): array
  {
    if(!isset($this->map_typeevent_hashes['Hook_modified']))
      return [];
    return \array_values(\array_unique($this->map_typeevent_hashes['Hook_modified']));
  }

  /*public function isSetHookInstall(): bool
  {
    return isset($this->map_typeevent_hashes['Hook_installed']);
  }

  public function isSetHookUninstall(): bool
  {
    return isset($this->map_typeevent_hashes['Hook_uninstalled']);
  }

  public function isSetHookNamespaceReset(): bool
  {
    return false;
  }*/
}