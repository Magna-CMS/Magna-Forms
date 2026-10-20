{{--
    The form block. Everything is escaped; the honeypot is a real input
    hidden from people (not display:none on a wrapper a bot skips), and
    the timestamp is the signed one the resolver issued. Both survive the
    page cache: the stamp carries no session, and the CSRF token is the
    visitor's own, fetched by core's magna::form-token component.
    A deleted form renders nothing.
--}}
@php
    $form = $block['_resolved'] ?? [];
    $heading = $block['data']['heading'] ?? '';
    $sent = session('magna_form_sent.'.($form['handle'] ?? ''));
@endphp

@if(!empty($form['handle']))
    <div class="magna-form">
        @if(is_string($heading) && $heading !== '')
            <h2 class="magna-form__heading">{{ $heading }}</h2>
        @endif

        @if(is_string($sent) && $sent !== '')
            <p class="magna-form__sent" role="status">{{ $sent }}</p>
        @else
            <form method="POST" action="{{ url('/forms/'.$form['handle']) }}" class="magna-form__form">
                {{-- Never @csrf: Pages caches this page for guests, so a baked
                     token would be the first visitor's, posted by everyone
                     after them. Each visitor fetches their own instead. --}}
                <x-magna::form-token/>
                <input type="hidden" name="_ts" value="{{ $form['timestamp'] }}">

                {{-- Honeypot: labelled for screen readers, hidden from sight. --}}
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <label for="magna-hp-{{ $form['handle'] }}">Leave this empty</label>
                    <input type="text" id="magna-hp-{{ $form['handle'] }}"
                           name="{{ $form['honeypot'] }}" tabindex="-1" autocomplete="off">
                </div>

                @foreach($form['fields'] as $field)
                    @php $inputId = 'magna-f-'.$form['handle'].'-'.$field['handle']; @endphp
                    <div class="magna-form__field magna-form__field--{{ $field['type'] }}">
                        <label for="{{ $inputId }}">
                            {{ $field['label'] }}@if($field['required'])<span aria-hidden="true">*</span>@endif
                        </label>

                        @if($field['type'] === 'textarea')
                            <textarea id="{{ $inputId }}" name="fields[{{ $field['handle'] }}]" rows="5"
                                      @if($field['required']) required @endif>{{ old('fields.'.$field['handle']) }}</textarea>
                        @elseif($field['type'] === 'select')
                            <select id="{{ $inputId }}" name="fields[{{ $field['handle'] }}]"
                                    @if($field['required']) required @endif>
                                <option value="">—</option>
                                @foreach($field['options'] as $option)
                                    <option value="{{ $option }}" @selected(old('fields.'.$field['handle']) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'checkbox')
                            <input type="checkbox" id="{{ $inputId }}" name="fields[{{ $field['handle'] }}]" value="1"
                                   @checked(old('fields.'.$field['handle'])) @if($field['required']) required @endif>
                        @else
                            <input type="{{ $field['type'] }}" id="{{ $inputId }}"
                                   name="fields[{{ $field['handle'] }}]"
                                   value="{{ old('fields.'.$field['handle']) }}"
                                   @if($field['required']) required @endif>
                        @endif

                        @error('fields.'.$field['handle'])
                            <p class="magna-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                <button type="submit" class="magna-form__submit">{{ $form['submitLabel'] }}</button>
            </form>
        @endif
    </div>
@endif
