<?php namespace Fooblog\Http\Middleware;

use Closure;
use App;
use Cache;
use PHP_CodeCoverage_Filter;
use PHP_CodeCoverage;

class Coverage
{

    /**
     * Checks if we have code coverage enabled and if so starts collection 
     * process.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Simple sanity check - we should never be collecting coverage data
        // in production
        if ( App::environment() == 'production' ) {
            return $next($request);
        }

        // It's possible for the durable storage layer (e.g. redis, filesystem,
        // etc...) to be shared by multiple stages. We don't want to overlap 
        // them so use coverage checking and storage keys that include the 
        // environment name
        $recordCoverageKey = 'recordCoverage:'.App::environment();
        $coverageKey = 'coverage:'.App::environment();

        if ( Cache::has($recordCoverageKey) ) {

            // We have to paths at this point
            // 1) We're already collecting data - in which case there is
            //    already a code coverage object in cache we can use
            // 2) This is the first request since collection has been
            //    enabled and we need to stand up a new coverage object
            if ( Cache::has($coverageKey) ) {
                $coverage = Cache::get($coverageKey);
            }
            else
            {
                // Explicitly white list the files you're interested in 
                // collecting coverage details about. Start by 
                // blacklisting everything
                $filter = new PHP_CodeCoverage_Filter;
                $filter->addDirectoryToBlacklist(base_path() . '/');

                // Then load in the white listed files from phpunit.xml
                $phpunit = simplexml_load_file( base_path() . '/phpunit.xml' );
                foreach ( $phpunit->filter->whitelist->children() as $directory ) {
                    $filter->addDirectoryToWhitelist( base_path() . '/'. (string)$directory, (string)$directory->attributes() );
                }
                
                $coverage = new PHP_CodeCoverage(null, $filter);
            }

            // Include the request method (e.g. GET, POST, etc..) and path
            // (e.g. /users) as the name of the test being run. This will 
            // be included in the coverage results (so we have some idea)
            // which calls ran what code...
            $coverage->start($request->method() . " " . $request->path());
            
            // Finally (and importantly) save the coverage data collected
            // back to some durable storage when the request completes
            register_shutdown_function(
                function($coverage) use ($coverageKey)
                {
                    $coverage->stop();

                    Cache::put($coverageKey, $coverage, 1440);
                }, $coverage 
            );
        }

        return $next($request);
    }

}
