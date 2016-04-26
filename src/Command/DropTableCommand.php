<?php

namespace Appkr\Importer\Command;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class DropTableCommand
 */
class DropTableCommand extends Command
{
    use EloquentTrait;
    use CommandTrait;

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName('table:drop')
            ->setDescription('테이블을 삭제합니다.')
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                '삭제할 테이블의 이름'
            );
    }

    /**
     * Run the command.
     *
     * @return mixed
     */
    protected function fire()
    {
        $table = $this->argument('table');

        if ($this->io->confirm("'$table' 테이블을 삭제하시겠습니까? ")) {
            $this->dropTable($table);

            return $this->io->success("'{$table}' 테이블을 삭제했습니다.");
        }
    }

    /**
     * Drop a table.
     *
     * @param $table
     * @return \Illuminate\Database\Schema\Blueprint
     */
    protected function dropTable($table)
    {
        if (! Capsule::schema()->hasTable($table)) {
            throw new \InvalidArgumentException(
                "'{$table}' 테이블이 없습니다. 테이블 이름을 다시 확인하세요."
            );
        }

        return Capsule::schema()->drop($table);
    }
}