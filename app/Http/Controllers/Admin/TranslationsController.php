<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;

class TranslationsController extends BaseController
{
    private $languages;

    private $attributes;

    private $files;

    public function __construct()
    {
        parent::__construct();

        $this->languages = array_sort(settings('languages'), function ($language) {
            return $language['title'];
        });

        $this->attributes = [
            ':days',
            ':driver',
            ':email',
            ':default',
            ':groups',
            ':geofences',
            ':attribute',
            ':date',
            ':max',
            ':min',
            ':format',
            ':other',
            ':digits',
            ':values',
            ':size',
        ];

        $this->files = [
            'front' => trans('admin.front_trans'),
            'admin' => trans('admin.admin_trans'),
            'global' => trans('admin.global_trans'),
            'validation' => trans('admin.validation_trans'),
        ];
    }

    public function index()
    {
        return View::make('admin::Translations.index')->with(['languages' => $this->languages]);
    }

    public function show($lang)
    {
        $lang = substr($lang, 0, 2);
        $files = $this->files;

        $language = settings('languages.'.$lang);

        return View::make('admin::Translations.show')->with(compact('files', 'lang', 'language'));
    }

    public function save()
    {
        $file = request()->get('file');
        $lang = request()->get('lang');
        $trans = request()->get('trans');

        if (! array_key_exists($file, $this->files)) {
            return ['status' => 0];
        }

        $en_translations = include base_path('resources/original_lang/en/'.$file.'.php');

        foreach ($trans as $key => $tran) {
            if (is_array($tran)) {
                foreach ($tran as $skey => $tran) {
                    foreach ($this->attributes as $atr) {
                        if (strpos($en_translations[$key][$skey], $atr) && strpos($tran, $atr) === false) {
                            return ['status' => 0, 'error' => ['key' => $key.'.'.$skey, 'message' => strtr(trans('front.attribute_missing'), [':attribute' => $atr])]];
                        }
                    }
                }
            } else {
                foreach ($this->attributes as $atr) {
                    if (strpos($en_translations[$key], $atr) && strpos($tran, $atr) === false) {
                        return ['status' => 0, 'error' => ['key' => $key, 'message' => strtr(trans('front.attribute_missing'), [':attribute' => $atr])]];
                    }
                }
            }
        }

        $out = parseTranslations($en_translations, $trans);

        @mkdir(storage_path('langs'));
        @chmod(storage_path('langs'), 0777);
        @mkdir(storage_path("langs/{$lang}"));
        @chmod(storage_path("langs/{$lang}"), 0777);
        file_put_contents(storage_path("langs/{$lang}/{$file}.php"), $out);
        @chmod(base_path("resources/lang/{$lang}/{$file}.php"), 0777);
        file_put_contents(base_path("resources/lang/{$lang}/{$file}.php"), $out);

        return ['status' => 1, 'message' => trans('front.successfully_saved')];
    }

    public function fileTrans()
    {
        $file = request()->get('file');
        $lang = request()->get('lang');

        if (! array_key_exists($file, $this->files)) {
            return trans('admin.translation_file_dont_exist');
        }

        $en_translations = include base_path('resources/original_lang/en/'.$file.'.php');
        $or_translations = include base_path('resources/original_lang/'.$lang.'/'.$file.'.php');
        $translations = include base_path('resources/lang/'.$lang.'/'.$file.'.php');

        return View::make('admin::Translations.trans')->with(compact('file', 'lang', 'translations', 'en_translations', 'or_translations'));
    }

    public function checkTrans()
    {
        $data = request()->all();

        if (! array_key_exists($data['file'], $this->files)) {
            return ['status' => 0];
        }

        $en_translations = include base_path('resources/original_lang/en/'.$data['file'].'.php');

        $arr = explode('.', $data['key']);
        $trans = $this->arrayRe($en_translations, $arr);

        foreach ($this->attributes as $atr) {
            if (strpos($trans, $atr) && strpos($data['val'], $atr) === false) {
                return ['status' => 0, 'error' => strtr(trans('front.attribute_missing'), [':attribute' => $atr])];
            }
        }

        return ['status' => 1];
    }

    private function arrayRe($arr, $keys)
    {
        if (! is_array($arr)) {
            return $arr;
        }
        $key = current($keys);
        array_shift($keys);

        return $this->arrayRe($arr[$key], $keys);
    }
}
