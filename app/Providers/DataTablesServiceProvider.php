<?php

namespace App\Providers;

use App\DataTables\Tables\Ticketeer;
use App\DataTables\Viewer;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class DataTablesServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [ ];


    public function getAvailableTables() {
        $path = app_path().'/DataTables/Tables/';

        $classes = array_map(
            function($i) {return str_replace('.php', '', $i); },
            array_diff(
                scandir($path),
                array('..', '.')
            )
        );


        return $classes;
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('\App\DataTables\Viewer', function($app) {
            return new Viewer($app);
        });

        foreach($this->getAvailableTables() as $tbl) {
            $this->app->bind("\App\DataTables\Tables\\".$tbl, function($app) use($tbl) {
                return new $tbl($app);
            });
        }
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(Router $router) {
        $router->group(['middleware' => 'web'], function() use($router) {
            // Register Default Routes for dataTables
            $router->post('/dataTables/stateSave', function(Auth $auth, Viewer $viewer) {
                $viewer->SetTableId($_REQUEST['tid']);
                $viewer->saveState($auth, $_REQUEST['payload']);
                response()->json([]);
            })->name('dataTables:stateSave');


            $router->post('/dataTables/stateLoad', function() {
                return response()->json([ false ]);
            })->name('dataTables:stateLoad');


            // Register Routes for Table Views
            foreach($this->getAvailableTables() as $tbl) {
                $router->post('/dataTables/'.$tbl, function(Auth $auth, App $app, Request $request) use($tbl) {
                    $table = resolve("App\DataTables\Tables\\".$tbl);
                    return $table->HandleRestRequest($request, []);
                })->name("App\DataTables\Tables\\".$tbl);

            }

        });
    }
}
