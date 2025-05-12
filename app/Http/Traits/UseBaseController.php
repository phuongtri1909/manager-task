<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\View;

trait UseBaseController
{
    public string $featureSlug  = '';
    public bool $canAccess      = false;
    public bool $canView        = false;
    public bool $canCreate      = false;
    public bool $canEdit        = false;
    public bool $canDelete      = false;

    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function view(string $viewPath, array $data = [])
    {
        $this->setPermissions($this->featureSlug);
        return view("pages.{$viewPath}", $data)->with('title', $this->title);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function json(array $data = [], int $code = 200)
    {
        return response()->json($data, $code);
    }

    /**
     * @return void
     */
    public function setPermissions(string $featureSlug): void
    {
        $this->canAccess    = can_access($featureSlug);
        $this->canView      = can_view($featureSlug);
        $this->canCreate    = can_create($featureSlug);
        $this->canEdit      = can_edit($featureSlug);
        $this->canDelete    = can_delete($featureSlug);

        View::share('canAccess', $this->canAccess);
        View::share('canView', $this->canView);
        View::share('canCreate', $this->canCreate);
        View::share('canEdit', $this->canEdit);
        View::share('canDelete', $this->canDelete);
    }
}
