<?php

namespace Appkr\Importer\Command;

use Illuminate\Database\Capsule\Manager as Capsule;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class ImportCommand
 */
class ImportCommand extends Command
{
    use EloquentTrait;
    use CommandTrait;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('csv:import')
            ->setDescription('CSV 파일을 읽어서 MySQL 테이블에 저장합니다.')
            ->addArgument(
                'csv',
                InputArgument::REQUIRED,
                'CSV 파일 경로'
            )
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                'MySQL 테이블 이름'
            );
    }

    /**
     * Run the command.
     *
     * @return mixed
     */
    protected function fire()
    {
        $path = $this->argument('csv');
        $table = $this->argument('table');

        $this->import($path, $table);

        return $this->io->success("'{$path}' 파일의 내용을 '{$table}' 테이블로 옮겼습니다.");
    }

    /**
     * Import given csv file to MySQL table.
     *
     * @param $path
     * @param $table
     */
    protected function import($path, $table)
    {
        // Validate user input.
        if (! file_exists($path)) {
            throw new InvalidArgumentException(
                "'{$path}'은(는) 찾을 수 없습니다. 계속 실패하면 절대 경로를 이용해서 다시 시도하세요."
            );
        }

        if (! Capsule::schema()->hasTable($table)) {
            throw new InvalidArgumentException(
                "'{$table}' 테이블을 찾을 수 없습니다. 먼저 table:create 명령으로 테이블을 만드세요."
            );
        }

        // Prepare the array keys
        // which will be used as a keys for the database insert payload.
        $columns = $this->getColumnListings($table);
        array_shift($columns);

        $csv = Reader::createFromPath($path);

        // Skip the first line
        // Usually it's the header of the table.
        $csv->setOffset(1);

        $csv->each(function ($row) use ($table, $columns) {
            $payload = [];

            foreach ($columns as $index => $column) {
                $payload[$column] = $row[$index];
            }

            if ($id = Capsule::table($table)->insertGetId($payload)) {
                $this->io->writeln(
                    sprintf('<info>저장 성공 %d:</info> %s', $id, json_encode($payload))
                );

                return true;
            }

            return false;
        });
    }
}