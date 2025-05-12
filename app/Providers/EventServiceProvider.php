<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            // dd(auth()->user());
            $menus = Menu::with('children', 'children.children', 'children.children.children')->whereNull('parent_id')->get();
            $this->formatMenu($menus)->each(function ($m) use ($event) {
                $event->menu->add($m);
            });
        });
    }

    public function formatMenu($menus, $list = null): Collection
    {
        $list = collect($list);

        foreach ($menus as $index => $menu) {
            if ($menu->children->isNotEmpty()) {
                $list[$index] = [];
                $submenu = $this->formatMenu($menu['children'], $list[$index])->toArray();
                $list[$index] = array_merge($menu['config'], ['submenu' => $submenu]);
            } else {
                $list->push($menu['config']);
            }
        }

        return $list;
    }
}
