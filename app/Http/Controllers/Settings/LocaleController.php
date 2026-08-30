<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\UpdateLocale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\LocaleUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;

final class LocaleController extends Controller
{
    public function __invoke(LocaleUpdateRequest $request, UpdateLocale $action): RedirectResponse
    {
        $locale = $request->validated('locale');
        $action->execute($request->user(), $locale);
        $request->session()->put('locale', $locale);

        App::setLocale($locale);

        return back()->with('success', __('Language updated.'));
    }
}
