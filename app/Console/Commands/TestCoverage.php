<?php namespace Fooblog\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App;
use Cache;
use PHP_CodeCoverage_Report_Clover;
use PHP_CodeCoverage_Report_HTML;

class TestCoverage extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'testing:coverage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initiate or write test coverage results';

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
    public function fire()
    {
        $recordCoverageKey = 'recordCoverage:' . $this->option('stage');
        $coverageKey = 'coverage:' . $this->option('stage');

        if ( $this->argument('action') == 'collect' ) {
            // Forget any coverage we currently have and begin recording
            Cache::forget($coverageKey);
            Cache::put($recordCoverageKey, true, 1440);

            $this->info("Collection of code coverage on {$this->option('stage')} environment initiated");
            return true;
        }

        if ( $this->argument('action') == 'write' ) {
            if ( Cache::has($coverageKey) ) {
                // Ensure the required directory exists
                if ( !file_exists(base_path().'/testresults/coverage/') ) {
                    mkdir(base_path().'/testresults/coverage/', 0770, true);
                }
                
                // Allocate more memory and prevent timeout as writing coverage
                // reports can take a lot of memory and a long time
                ini_set('memory_limit', '1024M');
                set_time_limit(0);

                $coverage = Cache::get($coverageKey);
                $writer = new PHP_CodeCoverage_Report_Clover;
                $writer->process($coverage, base_path().'/testresults/coverage/clover.xml');
                $this->info('Clover Coverage XML written to results/coverage/clover.xml');

                $writer = new PHP_CodeCoverage_Report_HTML;
                $writer->process($coverage, base_path().'/testresults/coverage/details');
                $this->info('Clover Coverage HTML written to results/coverage/details');
            }

            Cache::forget($recordCoverageKey);
            Cache::forget($coverageKey);
            return true;
        }

        $this->info('Please supply a valid argument (valid arguments are collect and write)');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [ 'action',
              InputArgument::REQUIRED,
              'specify whether to begin collecting coverage or write current coverage results' ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [ 'stage',
             's',
             InputOption::VALUE_OPTIONAL,
             'environment',
             App::environment() ]
        ];
    }

}
