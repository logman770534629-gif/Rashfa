<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function index(): View { return view('admin.settings.index', ['settings' => SiteSetting::orderBy('setting_key')->get()]); }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['setting_key' => ['required', 'alpha_dash', 'max:120', 'unique:site_settings,setting_key'], 'setting_value' => ['required', 'json'], 'description_ar' => ['nullable','string','max:300'], 'is_public' => ['nullable','boolean']]);
        SiteSetting::create(['setting_key' => $data['setting_key'], 'setting_value' => json_decode($data['setting_value'], true, 512, JSON_THROW_ON_ERROR), 'description_ar' => $data['description_ar'], 'is_public' => $request->boolean('is_public')]);
        return back()->with('status','تمت إضافة الإعداد.');
    }

    public function update(Request $request, SiteSetting $setting): RedirectResponse
    {
        $data = $request->validate(['setting_value' => ['required', 'json'], 'description_ar' => ['nullable','string','max:300'], 'is_public' => ['nullable','boolean']]);
        $setting->update(['setting_value' => json_decode($data['setting_value'], true, 512, JSON_THROW_ON_ERROR), 'description_ar' => $data['description_ar'], 'is_public' => $request->boolean('is_public')]);
        return back()->with('status','تم حفظ الإعداد.');
    }
}
