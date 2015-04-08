<?php namespace Fooblog\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App;

class MergeTestResults extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'testing:merge-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges Newman Tests and PHPUnit Tests into a single PHPUnit XML result.';

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
        // Read in the current php unit output and the new json result
        $phpunit = simplexml_load_file(base_path() . '/' . $this->argument('phpunit'));
        $testsuites = $phpunit->children()[0];
        $json = file_get_contents(base_path() . '/' . $this->argument('newman'));
        $results = json_decode($json);

        // For each call add a testsuite
        foreach( $results->results as $result ) 
        {
            $assertions = 0;
            $failures = 0;

            $XMLResult = $testsuites->addChild('testsuite');
            $XMLResult->addAttribute('name', $result->name);
            $XMLResult->addAttribute('time', $result->totalTime/1000); // time in ms

            // For each test in the suite add a testcase
            foreach( get_object_vars($result->tests) as $testCase => $testResult )
            {   
                $assertions++;

                $XMLTestCase = $XMLResult->addChild('testcase');
                $XMLTestCase->addAttribute('name', $testCase);

                // Since the vast majority of time is spent making the service
                // call, and because we don't have time for individual tests we
                // evenly divide total time by number of test cases 
                $XMLTestCase->addAttribute('time', $result->totalTime/count(get_object_vars($result->tests))/1000);

                // If the test failed then mark it as such in merged result and
                // keep track of the number of failures for use a few lines 
                // down
                if ( ! $testResult ) {
                    $failures++;
                    $failure = $XMLTestCase->addChild('failure', "Failed to assert Functional Test {$result->name} -> {$testCase}");
                }
            }

            $XMLResult->addAttribute('tests', $assertions);
            $XMLResult->addAttribute('assertions', $assertions);
            $XMLResult->addAttribute('failures', $failures);
        }

        $phpunit->asXML(base_path() . '/' . $this->argument('destination'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [ 
            [ 'newman', InputArgument::OPTIONAL, 'The source newman output JSON', 'testresults/newman/result.json' ],
            [ 'phpunit', InputArgument::OPTIONAL, 'The source phpunit xml result', 'testresults/phpunit/phpunit.xml' ],
            [ 'destination', InputArgument::OPTIONAL, 'The target phpunit xml file to merge into', 'testresults/phpunit/merged.xml' ],
        ];
    }

}
