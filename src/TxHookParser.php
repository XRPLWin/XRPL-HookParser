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
    $this->tx = $tx;
    $this->meta = isset($this->tx->meta) ? $this->tx->meta : $this->tx->metaData;

    $this->extractHooksFromMeta();
    $this->extractHooksFromContext();
  }

  private function extractHooksFromMeta()
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
                  if(!\is_object($hook)) continue;
                  
                  $this->addHook(
                    $hook->Hook->HookHash,
                    $AffectedNode->CreatedNode->NewFields->Account, //affected account
                    'Hook',
                    'created'
                  );
                }
                break;
              case 'HookState':
                //todo ?
                break;
            }
          }
        }
      }
      if(isset($AffectedNode->CreatedNode)) {
        //dd('test');
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
}