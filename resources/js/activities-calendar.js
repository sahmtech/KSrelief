import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function initActivitiesCalendar() {
    const el = document.getElementById('activitiesCalendar');
    if (!el) return;

    const eventsUrl = el.dataset.eventsUrl;
    const rescheduleBase = el.dataset.rescheduleUrl;
    const createUrl = el.dataset.createUrl;
    const canUpdate = el.dataset.canUpdate === '1';
    const isRtl = el.dataset.dir === 'rtl';

    const campaignFilter = document.getElementById('filterCampaign');
    const typeFilter = document.getElementById('filterType');

    const buildEventsUrl = (fetchInfo) => {
        const params = new URLSearchParams({
            start: fetchInfo.startStr.slice(0, 10),
            end: fetchInfo.endStr.slice(0, 10),
        });
        if (campaignFilter?.value) params.set('campaign_id', campaignFilter.value);
        if (typeFilter?.value) params.set('activity_type_id', typeFilter.value);
        return `${eventsUrl}?${params.toString()}`;
    };

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: isRtl ? 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' : 'prev,next today',
            center: 'title',
            right: isRtl ? 'prev,next today' : 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        locale: el.dataset.locale === 'ar' ? 'ar' : 'en',
        direction: isRtl ? 'rtl' : 'ltr',
        height: 'auto',
        editable: canUpdate,
        selectable: canUpdate,
        events: (info, success, failure) => {
            fetch(buildEventsUrl(info))
                .then((r) => r.json())
                .then(success)
                .catch(failure);
        },
        eventClick(info) {
            const props = info.event.extendedProps;
            const body = document.getElementById('activityDetailBody');
            const link = document.getElementById('activityDetailLink');
            if (body) {
                body.innerHTML = `
                    <p class="mb-1"><strong>${info.event.title}</strong></p>
                    <p class="text-muted mb-1">${props.campaign ?? ''} · ${props.type ?? ''}</p>
                    <p class="mb-1"><span class="badge bg-light text-dark border">${props.status ?? ''}</span></p>
                    ${props.location ? `<p class="mb-1"><i class="ti ti-map-pin"></i> ${props.location}</p>` : ''}
                    <p class="mb-0 small text-muted">${props.participants ?? 0} participants</p>`;
            }
            if (link && props.url) {
                link.href = props.url;
            }
            const modal = document.getElementById('activityDetailModal');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        },
        dateClick(info) {
            if (!canUpdate) return;
            const date = info.dateStr.slice(0, 10);
            window.location.href = `${createUrl}?activity_date=${date}`;
        },
        eventDrop(info) {
            rescheduleEvent(info.event);
        },
        eventResize(info) {
            rescheduleEvent(info.event);
        },
    });

    function rescheduleEvent(event) {
        const start = event.start;
        const end = event.end ?? start;
        const pad = (n) => String(n).padStart(2, '0');

        fetch(`${rescheduleBase}/${event.id}/reschedule`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                activity_date: `${start.getFullYear()}-${pad(start.getMonth() + 1)}-${pad(start.getDate())}`,
                start_time: `${pad(start.getHours())}:${pad(start.getMinutes())}`,
                end_time: `${pad(end.getHours())}:${pad(end.getMinutes())}`,
            }),
        }).then((r) => {
            if (!r.ok) {
                event.revert();
            }
        }).catch(() => event.revert());
    }

    calendar.render();
}

document.addEventListener('DOMContentLoaded', initActivitiesCalendar);
