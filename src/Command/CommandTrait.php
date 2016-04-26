<?php

namespace Appkr\Importer\Command;

use Illuminate\Validation\Factory;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Translation\Translator;

trait CommandTrait
{
    public $io;

    public $input;

    public $output;

    /**
     * CommandTrait constructor.
     */
    public function __construct()
    {
        $this->bootDatabase();

        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Available styles. (symfony/console ~2.8 feature)
        // title, section, text, comment, note, caution, listing, table,
        // ask, askHidden, confirm, choice, success, error, warning
        $this->io = new SymfonyStyle($input, $output);

        $this->input = $input;
        $this->output = $output;

        return $this->fire();
    }

    /**
     * Get all arguments.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->input->getArguments();
    }

    /**
     * Get an argument from the input.
     *
     * @param  string $key
     * @return string
     */
    public function argument($key)
    {
        return $this->input->getArgument($key);
    }

    /**
     * Get all options.
     *
     * @return array
     */
    public function options()
    {
        return $this->input->getOptions();
    }

    /**
     * Get an option from the input.
     *
     * @param  string $key
     * @return string
     */
    public function option($key)
    {
        return $this->input->getOption($key);
    }

    /**
     * Do the validation job.
     *
     * @param array $data
     * @return bool
     */
    public function validate($data)
    {
        $factory = new Factory(new Translator('en'));

        $v = $factory->make($data, static::rules(), static::messages(), static::attributes());

        if ($v->fails()) {
            throw new InvalidArgumentException(
                $v->errors()->first()
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function messages()
    {
        return [
            'numeric' => '"":attribute" 값은 숫자만 허용합니다.',
            'regex' => '":attribute"의 형식이 틀렸습니다.',
            'min' => '":attribute"는 :min보다 큰 값만 허용합니다.',
            'max' => '":attribute"는 :max보다 작은 값만 허용합니다.',
            'in' => '":attribute"에 허용되지 않는 값을 입력했습니다.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function attributes()
    {
        return [
            'table' => '테이블(table)',
            'schema.*.name' => '컬럼 이름',
            'schema.*.type' => '컬럼 형식',
            'direction' => '정렬 방향(--direction)',
            'page' => '페이지(--page)',
            'limit' => '목록 개수(--limit)',
        ];
    }
}
