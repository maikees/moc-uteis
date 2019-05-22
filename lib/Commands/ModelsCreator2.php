<?php

namespace MOCUtils\Helpers\Commands;

use Illuminate\Console\Command;

class ModelsCreator2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moc:models:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create models from database configuration.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //ToDo Ler tabelas do banco.
        //ToDo Ler colunas das tabelas.
    }
}
