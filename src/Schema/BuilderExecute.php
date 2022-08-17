<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

trait BuilderExecute
{
    /**
     * Execute commands.
     */
    public function execute(string $language, string $body): void
    {
        $this->getConnection()->statement(
            sprintf(
                "DO $$ %1\$s $$ LANGUAGE %2\$s;",
                $body,
                $language,
            )
        );
    }
}