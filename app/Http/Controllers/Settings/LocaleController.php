<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\UpdateLocale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\LocaleUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

final class LocaleController extends Controller
{
    public function __invoke(LocaleUpdateRequest $request, UpdateLocale $action): RedirectResponse
    {
        $locale = $request->validated('locale');
        $action->execute($request->user(), $locale);
        $request->session()->put('locale', $locale);

        App::setLocale($locale);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Language updated.'),
        ]);

        return back();
    }
}
