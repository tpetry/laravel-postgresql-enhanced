<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

trait BuilderGrant
{
    public function grantConnect(string $user): void
    {
        $this->getConnection()->statement("grant connect on database {$this->getConnection()->getSchemaGrammar()->wrap($this->getConnection()->getDatabaseName())} to {$this->getConnection()->getSchemaGrammar()->wrap($user)}");
    }

    public function grantReadOnly(string $user, string $schema = 'public', bool $includeFuture = false): void
    {
        $this->getConnection()->statement("grant usage on schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)} to {$this->getConnection()->getSchemaGrammar()->wrap($user)}");
        $this->getConnection()->statement("grant select on all tables in schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)} to {$this->getConnection()->getSchemaGrammar()->wrap($user)}");

        if ($includeFuture) {
            $this->getConnection()->statement("alter default privileges in schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)} grant select on tables to {$this->getConnection()->getSchemaGrammar()->wrap($user)}");
        }
    }

    public function grantReadWrite(string $user, string $schema = 'public', bool $includeFuture = false): void
    {
        $this->getConnection()->statement("grant usage on schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)} to {$this->getConnection()->getSchemaGrammar()->wrap($user)}");
        $this->getConnection()->statement("grant select, insert, update, delete on all tables in schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)} to {$this->getConnection()->getSchemaGrammar()->wrap($user)}");
        $this->getConnection()->statement("grant usage on all sequences in schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)} to {$this->getConnection()->getSchemaGrammar()->wrap($user)}");

        if ($includeFuture) {
            $this->getConnection()->statement("alter default privileges in schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)} grant select, insert, update, delete on tables to {$this->getConnection()->getSchemaGrammar()->wrap($user)}");
            $this->getConnection()->statement("alter default privileges in schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)} grant usage on sequences to {$this->getConnection()->getSchemaGrammar()->wrap($user)}");
        }
    }
}
