<?php

namespace Appkr\Importer\Command;

use Appkr\Importer\Parsers\SchemaParser;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class CreateTableCommand
 */
class CreateTableCommand extends Command implements Validatable
{
    use EloquentTrait;
    use CommandTrait;

    /**
     * {@inheritdoc}
     */
    public static function rules()
    {
        return [
            'schema.*.name' => 'required|min:2',
            'schema.*.type' => 'required|in:boolean,date,datetime,float,integer,string,text,timestamp',
        ];
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName('table:create')
            ->setDescription('새 테이블을 만듭니다.')
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                '만들 테이블의 이름'
            )
            ->addOption(
                'schema',
                'S',
                InputOption::VALUE_REQUIRED,
                '테이블 스키마(컴럼:형식). e.g.. --schema="foo:string,bar:text,baz:timestamp"'
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
        $schema = $this->option('schema');

        // Parse command option.
        if ($schema) {
            $schema = (new SchemaParser)->parse($schema);
        }

        $this->validate(compact('schema'));

        $this->createTable($table, $schema);

        return $this->io->success("'{$table}' 테이블을 만들었습니다.");
    }

    /**
     * Create a table.
     *
     * @param string $table Name of the table to create.
     * @param array $schema List of schema of the table.
     * @return \Illuminate\Database\Schema\Blueprint
     */
    protected function createTable($table, $schema)
    {
        if (Capsule::schema()->hasTable($table)) {
            throw new InvalidArgumentException(
                "'{$table}'은(는) 이미 있는 테이블입니다. 이름을 바꾸어 다시 시도하세요."
            );
        }

        return Capsule::schema()->create($table, function (Blueprint $table) use ($schema) {
            $table->increments('id');

            foreach ($schema as $column) {
                $table->{$column['type']}($column['name'])->nullable();
            }
        });
    }

    /**
     * Currently not use. Laid here for future purpose.
     *
     * @param $column
     * @return string
     */
    private function buildSyntax($column)
    {
        $syntax = sprintf("\$table->%s('%s')", $column['type'], $column['name']);

        // If there are arguments for the schema type, like decimal('amount', 5, 2)
        // then we have to remember to work those in.
        if ($column['arguments']) {
            $syntax = substr($syntax, 0, -1) . ', ';
            $syntax .= implode(', ', $column['arguments']) . ')';
        }

        foreach ($column['options'] as $method => $value) {
            $syntax .= sprintf("->%s(%s)", $method, $value === true ? '' : $value);
        }

        return $syntax .= ';';
    }
}