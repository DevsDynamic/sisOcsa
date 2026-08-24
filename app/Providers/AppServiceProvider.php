<?php

namespace App\Providers;

use Illuminate\Support\Facades\View; // Importa la clase View
use Illuminate\Support\ServiceProvider;
use App\Models\TypeDocument; // Importa los modelos necesarios
use App\Models\TypePerson;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Carbon\Carbon::setLocale(config('app.locale'));

        Gate::before(function ($user) {
            return $user->is_system_owner ? true : null;
        });

         // Definir un view composer para las vistas específicas
        View::composer(
            ['people.index', 'people.index-cp', 'people.index-co'], // Lista de vistas
            function ($view) {
                $typeDocuments = TypeDocument::select(
                    'type_documents.id AS id',
                    'type_documents.name AS name',
                    'type_documents.max_length AS max_length'
                )->where('type_documents.status', 1)->get();

                $typePeople = TypePerson::select(
                    'type_people.id AS id',
                    'type_people.name AS name',
                    'type_people.description AS description'
                )->where('type_people.status', 1)->get();

                $view->with(compact('typeDocuments', 'typePeople'));
            }
        );
    }
}
