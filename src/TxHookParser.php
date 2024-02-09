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
  private array $map_created_hook_details = [];
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
        null
      );
    }
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
                  'created',
                  null
                );
                $this->addCreatedHookDetails($AffectedNode->CreatedNode->NewFields->HookHash,$AffectedNode->CreatedNode);
                break;
              case 'Hook':
                foreach($AffectedNode->CreatedNode->NewFields->Hooks as $position => $hook) {
                  if(isset($hook->Hook->HookHash)) {
                    $this->addHook(
                      $hook->Hook->HookHash,
                      $AffectedNode->CreatedNode->NewFields->Account, //affected account
                      'Hook',
                      'installed',
                      $position
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
                  (isset($AffectedNode->ModifiedNode->FinalFields) && $AffectedNode->ModifiedNode->FinalFields)?$AffectedNode->ModifiedNode->FinalFields->Hooks:null,
                  isset($AffectedNode->ModifiedNode->PreviousFields)
                );
                if(count($parsed['added'])) {
                  foreach($parsed['added'] as $h => $hv) {
                    $h = \explode('_',$h);
                    $this->addHook(
                      $h[1],
                      $AffectedNode->ModifiedNode->FinalFields->Account, //affected account
                      'Hook',
                      'installed',
                      (int)$h[0]
                    );
                  }
                }

                if(count($parsed['removed'])) {
                  foreach($parsed['removed'] as $h => $hv) {
                    $h = \explode('_',$h);
                    $this->addHook(
                      $h[1],
                      $AffectedNode->ModifiedNode->FinalFields->Account, //affected account
                      'Hook',
                      'uninstalled',
                      (int)$h[0]
                    );
                  }
                }

                if(count($parsed['modified'])) {
                  foreach($parsed['modified'] as $h => $hv) {
                    $h = \explode('_',$h);
                    $this->addHook(
                      $h[1],
                      $AffectedNode->ModifiedNode->FinalFields->Account, //affected account
                      'Hook',
                      'modified',
                      (int)$h[0]
                    );
                  }
                }
                //dd($parsed['unmodified']);
                if(count($parsed['unmodified'])) {
                  foreach($parsed['unmodified'] as $h => $hv) {
                    $h = \explode('_',$h);
                    $this->addHook(
                      $h[1],
                      $AffectedNode->ModifiedNode->FinalFields->Account, //affected account
                      'Hook',
                      'unmodified',
                      (int)$h[0]
                    );
                  }
                }
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
                  'destroyed',
                  null
                );
                
                break;
              case 'Hook':
                foreach($AffectedNode->DeletedNode->FinalFields->Hooks as $position => $hook) {
                  if(isset($hook->Hook->HookHash)) {
                    $this->addHook(
                      $hook->Hook->HookHash,
                      $AffectedNode->DeletedNode->FinalFields->Account, //affected account
                      'Hook',
                      'uninstalled',
                      $position
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
          'target',
          null
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

  /**
   * Takes position in Hooks array and extracts change for single position.
   * @return array [type,old,new] or []
   */
  private function hookChangesFromModifiedHookNodePosition(?\stdClass $prev,?\stdClass $final, bool $is_modify): array
  {
    //normalize empty slot
    if(isset($prev->Hook) && !isset($prev->Hook->HookHash))
      $prev = null;
    if(isset($final->Hook) && !isset($final->Hook->HookHash))
      $final = null;

    if($prev === null && $final === null) {
      //no change
      return [
        'type' => 'unmodified_null',
        'focus' => null,
        'old' => null,
        'new' => null
      ];
    } else if($prev === null && isset($final->Hook)) {
      //install
      if($is_modify) {
        return [
          'type' => 'unmodified',
          'old' => $final->Hook->HookHash,
          'new' => $final->Hook->HookHash
        ];
      }
      return [
        'type' => 'added',
        'focus' => $final->Hook->HookHash,
        'old' => null,
        'new' => $final->Hook->HookHash
      ];
    } else if(isset($prev->Hook) && $final === null) {
      //uninstall
      return [
        'type' => 'removed',
        'focus' => $prev->Hook->HookHash,
        'old' => $prev->Hook->HookHash,
        'new' => null
      ];

    } else if(isset($prev->Hook) && isset($final->Hook)) {
      //modify
      $prevContents = $this->normalizeHookNode($prev->Hook);
      $prevHash = \json_encode($prevContents);
      $finalContents = $this->normalizeHookNode($final->Hook);
      $finalHash = \json_encode($finalContents);
      if($prevHash != $finalHash) {
        return [
          'type' => 'modified',
          'old' => $prev->Hook->HookHash,
          'new' => $final->Hook->HookHash
        ];
      } else {
        return [
          'type' => 'unmodified',
          'old' => $prev->Hook->HookHash,
          'new' => $final->Hook->HookHash
        ];
      }
    }
  }

  private function hookChangesFromModifiedHookNode(?array $prev, ?array $final, bool $prevFieldsSet): array
  {
    //flag to indicate modification of hook (prev does not exist in modified node)
    //ledger does not include previous fields when there is no changes
    $is_modify = !$prevFieldsSet;
    
    $prev = $prev === null?[]:$prev;
    $final = $final === null?[]:$final;

    $r = ['added' => [], 'removed' => [], 'unmodified' => [], 'modified' => []];

    $results = [];
    $pos = 0;
    while ($pos < 10) {
      $results[$pos] = $this->hookChangesFromModifiedHookNodePosition(
        isset($prev[$pos]) ? $prev[$pos]:null,
        isset($final[$pos]) ? $final[$pos]:null,
        $is_modify
      );
      $pos++;
    }
    foreach($results as $pos => $result) {
      if($result['type'] == 'unmodified_null') {
        //skip empty position
      } else if($result['type'] == 'added') {
        $r['added'][$pos.'_'.$result['new']] = $pos;
      } else if($result['type'] == 'removed') {
        $r['removed'][$pos.'_'.$result['old']] = $pos;
      } else if($result['type'] == 'modified') {
        if($result['old'] != $result['new']) {
          //different hashes, one removed other added
          $r['removed'][$pos.'_'.$result['old']] = $pos;
          $r['added'][$pos.'_'.$result['new']] = $pos;
        } else {
          //same hashes new/old one is modified
          $r['modified'][$pos.'_'.$result['new']] = $pos;
        }
      } else if($result['type'] == 'unmodified') {
        $r['unmodified'][$pos.'_'.$result['new']] = $pos;
      } else {
        throw new \Exception('Unhandled '.$result['type']);
      }
    }
    return $r;
  }
  
  
  private function hookChangesFromModifiedHookNode_Old(?array $prev, ?array $final, bool $prevFieldsSet): array
  {
    //flag to indicate modification of hook (prev does not exist in modified node)
    //ledger does not include previous fields when there is no changes
    $is_modify = !$prevFieldsSet;
    
    $prev = $prev === null?[]:$prev;
    $final = $final === null?[]:$final;
    
    $r = ['added' => [], 'removed' => [], 'unmodified' => [], 'modified' => []];
    $tracker = [];
    $postracker = ['prev' => [],'final' => []];

    # POSTRACKER, eg hook index per hook, each first hook has index of 0
    # if there is two same hook hashes they will have indexes 0 and 1
    # Differentiate same hooks in different positions
    foreach($prev as $p) {
      if(!isset($p->Hook->HookHash)) continue;
      if(!isset($postracker['prev'][$p->Hook->HookHash]))
        $postracker['prev'][$p->Hook->HookHash] = -1;
      $postracker['prev'][$p->Hook->HookHash]++;
    }

    foreach($final as $p) {
      if(!isset($p->Hook->HookHash)) continue;
      if(!isset($postracker['final'][$p->Hook->HookHash]))
        $postracker['final'][$p->Hook->HookHash] = -1;
      $postracker['final'][$p->Hook->HookHash]++;
    }
    # POSTRACKER END
    foreach($prev as $p) {
      if(!isset($p->Hook->HookHash)) continue;
      $h = $p->Hook->HookHash;
      //Index:
      $hi = $postracker['prev'][$h];
      $postracker['prev'][$h]--; //one index exausted
      //Index end
      $contents = $this->normalizeHookNode($p->Hook);
      $tracker[$h.'_'.$hi] = ['state' => 1, 'hsh' => [\json_encode($contents)]];
    }
    
    foreach($final as $p) {
      if(!isset($p->Hook->HookHash)) continue;
      $h = $p->Hook->HookHash;
      //Index:
      $hi = $postracker['final'][$h];
      $postracker['final'][$h]--; //one index exausted
      //Index end
      $contents = $this->normalizeHookNode($p->Hook);
      if(!isset($tracker[$h.'_'.$hi])) {
        $tracker[$h.'_'.$hi] = ['state' => 0, 'hsh' => [\json_encode($contents)]];
      } else {
        $tracker[$h.'_'.$hi]['hsh'][] = \json_encode($contents);
        $tracker[$h.'_'.$hi]['state']++;
      }
    }

    foreach($tracker as $h => $data) {
      $state = $data['state'];
      if($state === 0) {
        //hook added
        if($is_modify)
          $r['modified'][$h] = true;
        else
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
          foreach($v as $hData) {
            $this->addHook(
              $hData[0],
              $this->tx->Account, //affected account
              'HookExecution',
              'invoker',
              null
            );
          }
        }
      }
    }
  }

  private function addHook(string $hookHash, ?string $account, string $fromType, string $event, ?int $position): void
  {
    $account = $account !== null ? $account:'NULL';
    $this->map_full[$account][$fromType][$event][] = [$hookHash,$position];

    $this->map_hashes[$hookHash] = true;
    $this->map_typeevent_hashes[$fromType.'_'.$event][] = [$hookHash,$position];

    if(!isset($this->map_hash_accounts[$hookHash]))
      $this->map_hash_accounts[$hookHash] = [];

    if($account !== 'NULL') {
      $this->map_hash_accounts[$hookHash][] = $account;

      if(!isset($this->map_account_hash[$account]))
        $this->map_account_hash[$account] = [];
      $this->map_account_hash[$account][] = [$hookHash,$position];
    }
  }

  private function addCreatedHookDetails(string $hookHash, \stdClass $data)
  {
    $this->map_created_hook_details[$hookHash] = $data;
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
   * Lookup map by address type and event respectivly
   * from data added by addHook() method.
   * @param ?string $address - null to lookup hooks without account context
   * @param string $fromType
   * @param string $event
   * @return array
   */
  public function lookup(?string $address, string $fromType, string $event): array
  {
    $address = $address !== null ? $address:'NULL';
    return isset($this->map_full[$address][$fromType][$event]) ? $this->map_full[$address][$fromType][$event]:[];
  }

  /**
   * Get list of hooks of affected provided account.
   * @return array
   */
  public function accountHooks(string $address): array
  {
    if(!isset($this->map_account_hash[$address]))
      return [];

    $hooks = \array_values($this->map_account_hash[$address]);
    
    $r = [];
    foreach($hooks as $hData) {
      $r[] = $hData[0];
    }
    return $r;
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
   * @return array
   */
  public function createdHooks(): array
  {
    if(!isset($this->map_typeevent_hashes['HookDefinition_created']))
      return [];

    $r = [];
    foreach($this->map_typeevent_hashes['HookDefinition_created'] as $hData) {
      $r[] = $hData[0];
    }
    return \array_unique($r);
  }

  /**
   * Get detailed hook definitions
   * @return array
   */
  public function createdHooksDetailed(): array
  {
    return $this->map_created_hook_details;
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

    $r = [];
    foreach($this->map_typeevent_hashes['HookDefinition_destroyed'] as $hData) {
      $r[] = $hData[0];
    }
    return \array_unique($r);
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
    $hooks = $this->installedHooksPos();
    $r = [];
    foreach($hooks as $hData) {
      $r[] = $hData[0];
    }
    return $r;
  }

  public function installedHooksPos(): array
  {
    if(!isset($this->map_typeevent_hashes['Hook_installed']))
      return [];
    return \array_values($this->map_typeevent_hashes['Hook_installed']);
  }


  /**
   * Get list of hooks and number of installations.
   * @return array [[ hookhash => num_installs], ...]
   */
  public function installedHooksStats(): array
  {
    if(!isset($this->map_typeevent_hashes['Hook_installed']))
      return [];
    $collect = [];
    foreach($this->map_typeevent_hashes['Hook_installed'] as $hData) {
      $h = $hData[0];
      if(!isset($collect[$h]))
        $collect[$h] = 0;
      $collect[$h]++;
    }
    return $collect;
  }

  /**
   * Get list of uninstalled hooks from account in this transaction.
   */
  public function uninstalledHooks(): array
  {
    $hooks = $this->uninstalledHooksPos();
    $r = [];
    foreach($hooks as $hData) {
      $r[] = $hData[0];
    }
    return $r;
  }

  public function uninstalledHooksPos(): array
  {
    if(!isset($this->map_typeevent_hashes['Hook_uninstalled']))
      return [];
    return \array_values($this->map_typeevent_hashes['Hook_uninstalled']);
  }

  /**
   * Get list of hooks and number of installations.
   * @return array [[ hookhash => num_installs], ...]
   */
  public function uninstalledHooksStats(): array
  {
    if(!isset($this->map_typeevent_hashes['Hook_uninstalled']))
      return [];
    $collect = [];
    foreach($this->map_typeevent_hashes['Hook_uninstalled'] as $hData) {
      $h = $hData[0];
      if(!isset($collect[$h]))
        $collect[$h] = 0;
      $collect[$h]++;
    }
    return $collect;
  }

  public function modifiedHooks(): array
  {
    $hooks = $this->modifiedHooksPos();
    $r = [];
    foreach($hooks as $hData) {
      $r[] = $hData[0];
    }
    return $r;
  }

  public function modifiedHooksPos(): array
  {
    if(!isset($this->map_typeevent_hashes['Hook_modified']))
      return [];
    return \array_values($this->map_typeevent_hashes['Hook_modified']);
  }

  /**
   * Hooks which by SetHook action was reinstalled but contents was not changed.
   */
  public function unmodifiedHooks(): array
  {
    $hooks = $this->unmodifiedHooksPos();
    $r = [];
    foreach($hooks as $hData) {
      $r[] = $hData[0];
    }
    return $r;
  }

  public function unmodifiedHooksPos(): array
  {
    if(!isset($this->map_typeevent_hashes['Hook_unmodified']))
      return [];
    return \array_values($this->map_typeevent_hashes['Hook_unmodified']);
  }


  # Static

  /**
   * Takes just created hookdefinition and returns key value parameters.
   * @param \stdClass $hookDefinition
   * @return array
   */
  public static function toParams(\stdClass $hookDefinition): array
  {
    $params = [];
    if(!isset($hookDefinition->NewFields->HookParameters))
      return $params;

    $HookParameters = $hookDefinition->NewFields->HookParameters;
    foreach($HookParameters as $p) {
      $params[$p->HookParameter->HookParameterName] = $p->HookParameter->HookParameterValue;
    }
    return $params;
  }
}