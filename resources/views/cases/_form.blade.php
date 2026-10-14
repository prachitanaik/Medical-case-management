{{-- One form used for both create and edit. Expects: $case, $statuses, $priorities, $action, $method --}}
<form method="POST" action="{{ $action }}" class="card" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="case_number">Case Number</label>
            <input id="case_number" type="text" name="case_number" maxlength="50"
                   value="{{ old('case_number', $case->case_number) }}"
                   class="{{ $errors->has('case_number') ? 'invalid' : '' }}" required>
            @error('case_number') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="patient_reference">Patient Reference (dummy code)</label>
            <input id="patient_reference" type="text" name="patient_reference" maxlength="100"
                   value="{{ old('patient_reference', $case->patient_reference) }}"
                   class="{{ $errors->has('patient_reference') ? 'invalid' : '' }}" required>
            @error('patient_reference') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="surgeon_name">Surgeon Name</label>
            <input id="surgeon_name" type="text" name="surgeon_name" maxlength="150"
                   value="{{ old('surgeon_name', $case->surgeon_name) }}"
                   class="{{ $errors->has('surgeon_name') ? 'invalid' : '' }}" required>
            @error('surgeon_name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="implant_type">Implant Type</label>
            <input id="implant_type" type="text" name="implant_type" maxlength="150"
                   value="{{ old('implant_type', $case->implant_type) }}"
                   class="{{ $errors->has('implant_type') ? 'invalid' : '' }}" required>
            @error('implant_type') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status" class="{{ $errors->has('status') ? 'invalid' : '' }}" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $case->status?->value) === $status->value)>{{ $status->value }}</option>
                @endforeach
            </select>
            @error('status') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" class="{{ $errors->has('priority') ? 'invalid' : '' }}" required>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected(old('priority', $case->priority?->value) === $priority->value)>{{ $priority->value }}</option>
                @endforeach
            </select>
            @error('priority') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="surgery_date">Surgery Date (optional)</label>
            <input id="surgery_date" type="date" name="surgery_date"
                   value="{{ old('surgery_date', $case->surgery_date?->format('Y-m-d')) }}"
                   class="{{ $errors->has('surgery_date') ? 'invalid' : '' }}">
            @error('surgery_date') <div class="error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">{{ $submit }}</button>
        <a class="btn btn-light" href="{{ route('cases.index') }}">Cancel</a>
    </div>
</form>
