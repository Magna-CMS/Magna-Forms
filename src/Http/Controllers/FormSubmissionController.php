<?php

declare(strict_types=1);

namespace Magna\Forms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Magna\Forms\FormSubmissionHandler;
use Magna\Forms\Models\Form;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The public submit endpoint. Thin by rule: resolve the form, hand the
 * request to the handler, shape the response — every decision about spam,
 * validation, storage and notification lives in FormSubmissionHandler.
 *
 * On the web group, so a posted form carries the session's CSRF token
 * like any other form on the site.
 */
final class FormSubmissionController
{
    public function __construct(private readonly FormSubmissionHandler $handler) {}

    public function store(Request $request, string $handle): JsonResponse|RedirectResponse
    {
        $form = Form::query()->where('handle', $handle)->first();
        if ($form === null) {
            throw new NotFoundHttpException('Form not found.');
        }

        $result = $this->handler->handle($form, $request);

        if ($request->expectsJson()) {
            return response()->json($result, $result['ok'] ? 200 : 422);
        }

        if (! $result['ok']) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return back()->with('magna_form_sent.'.$form->handle, $result['message']);
    }
}
