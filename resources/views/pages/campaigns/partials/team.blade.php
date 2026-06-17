<div class="row g-3">
    <div class="col-lg-8">
        <x-card :title="__('members.team.title')" :subtitle="__('members.team.subtitle')" :flush="true">
            @if($campaign->campaignMemberAssignments->isEmpty())
                <div class="text-center text-muted py-4">{{ __('members.team.empty') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('members.table.name') }}</th>
                                <th>{{ __('members.fields.assigned_role') }}</th>
                                <th>{{ __('members.table.specialty') }}</th>
                                <th>{{ __('members.fields.assigned_from') }}</th>
                                <th>{{ __('members.fields.assigned_to') }}</th>
                                <th class="text-end">{{ __('members.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaign->campaignMemberAssignments as $assignment)
                                @php $member = $assignment->member; @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <x-member-avatar :member="$member" size="sm" />
                                            <div>
                                                <a href="{{ route('medical-staff.members.show', $member) }}" class="fw-medium text-decoration-none">
                                                    {{ $member->full_name }}
                                                </a>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $member->mobile }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $assignment->assigned_role ?? $member->memberRole?->name ?? '—' }}</td>
                                    <td>{{ $member->specialty?->name ?? '—' }}</td>
                                    <td>{{ $assignment->assigned_from?->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $assignment->assigned_to?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="text-end">
                                        @can('assignCampaign', $member)
                                            <form method="POST" action="{{ route('campaigns.team.remove', [$campaign, $member]) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="{{ __('members.messages.confirm_remove') }}">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    @can('assignCampaign', \App\Models\Member::make())
        <div class="col-lg-4">
            <x-card :title="__('members.team.add_member')">
                @if($availableMembers->isEmpty())
                    <div class="text-muted text-center py-3" style="font-size: 0.875rem;">{{ __('members.campaigns_page.no_available') }}</div>
                @else
                    <form method="POST" action="{{ route('campaigns.team.assign', $campaign) }}">
                        @csrf
                        <x-form-input :label="__('members.table.name')" name="member_id" type="select" required>
                            <option value="">{{ __('members.placeholders.select_member') }}</option>
                            @foreach($availableMembers as $member)
                                <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>
                                    {{ $member->full_name }} ({{ $member->memberRole?->name }})
                                </option>
                            @endforeach
                        </x-form-input>
                        <x-form-input :label="__('members.fields.assigned_role')" name="assigned_role" :value="old('assigned_role')" />
                        <div class="row g-0">
                            <div class="col-md-6 pe-md-2">
                                <x-form-input :label="__('members.fields.assigned_from')" name="assigned_from" type="date" :value="old('assigned_from', now()->toDateString())" />
                            </div>
                            <div class="col-md-6 ps-md-2">
                                <x-form-input :label="__('members.fields.assigned_to')" name="assigned_to" type="date" :value="old('assigned_to')" />
                            </div>
                        </div>
                        <x-form-input :label="__('members.fields.notes')" name="notes" type="textarea" :value="old('notes')" />
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="ti ti-plus me-1"></i> {{ __('members.actions.quick_add') }}
                        </button>
                    </form>
                @endif
            </x-card>
        </div>
    @endcan
</div>
