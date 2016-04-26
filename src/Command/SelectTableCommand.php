<?php

namespace Appkr\Importer\Command;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SelectTableCommand extends Command implements Validatable
{
    use EloquentTrait;
    use CommandTrait;

    /**
     * {@inheritdoc}
     */
    public static function rules()
    {
        return [
            'direction' => 'in:asc,desc',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1|max:100',
        ];
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName('table:select')
            ->setDescription('테이블의 내용을 조회합니다.')
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                '조회할 테이블의 이름'
            )
            ->addOption(
                'page',
                'P',
                InputOption::VALUE_OPTIONAL,
                '조회할 페이지 e.g. 한번에 10개씩 조회할 때 2페이지는 11~20까지를 의미함',
                1
            )
            ->addOption(
                'sort',
                'S',
                InputOption::VALUE_OPTIONAL,
                '정렬 기준으로 사용할 컬럼 이름',
                'id'
            )
            ->addOption(
                'direction',
                'D',
                InputOption::VALUE_OPTIONAL,
                '정렬 방향 [asc|desc]',
                'asc'
            )
            ->addOption(
                'limit',
                'L',
                InputOption::VALUE_OPTIONAL,
                '한번에 조회할 레코드 수',
                10
            )
            ->addOption(
                'column',
                'C',
                InputOption::VALUE_OPTIONAL,
                '조회할 컬럼 선택',
                '*'
            );
    }

    /**
     * Run the command.
     *
     * @return mixed
     * @throws \InvalidOptionException
     */
    protected function fire()
    {
        $columns = preg_split('/,\s?(?![^()]*\))/', $this->option('column'));
        $headers = $this->getColumnListings($this->argument('table'));

        if ($columns !== ['*']) {
            // Override table headers when there is  --column option.
            $headers = $columns;
        }

        $collection = $this->fetch($columns);

        return $this->render($headers, $collection);
    }

    /**
     * Query database to get the collection.
     *
     * @param $columns
     * @return array|static[]
     * @throws \InvalidOptionException
     */
    protected function fetch($columns)
    {
        $table = $this->argument('table');
        $sort = $this->option('sort');
        $direction = $this->option('direction');
        $page = (int) $this->option('page');
        $limit = (int) $this->option('limit');
        $offset = ($page * $limit) - $limit;

        // Validate user input.
        $this->validate(compact('page', 'limit', 'direction'));

        if (! Capsule::schema()->hasColumn($table, $sort)) {
            throw new \InvalidOptionException(
                "'{$sort}'은(는) 없는 컬럼입니다."
            );
        }

        if ($columns !== ['*']) {
            foreach ($columns as $column) {
                if (! Capsule::schema()->hasColumn($table, $column)) {
                    throw new \InvalidOptionException(
                        "'{$column}'은(는) 없는 컬럼입니다."
                    );
                }
            }
        }

        return Capsule::table($table)
            ->select($columns)
            ->orderBy($sort, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Render the given collection into a table.
     *
     * @param $collection
     * @return mixed
     */
    protected function render($headers, $collection)
    {
        $collection = json_decode(json_encode($collection), true);

        return $this->io->table($headers, $collection);
    }
}