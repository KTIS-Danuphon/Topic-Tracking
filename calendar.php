<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปฏิทิน - Topic Tracking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --navbar-height: 70px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding-top: var(--navbar-height);
        }

        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 2rem;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .main-content {
            padding: 2rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        .calendar-header {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .calendar-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .month-nav {
            display: flex;
            gap: 0.5rem;
        }

        .btn-nav {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-nav:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
        }

        .btn-today {
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            border: 2px solid var(--primary-color);
            background: white;
            color: var(--primary-color);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-today:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        .calendar-filters {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .filter-chip {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: 2px solid #e2e8f0;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .filter-chip:hover {
            border-color: var(--primary-color);
        }

        .filter-chip.active {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
        }

        .calendar-container {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e2e8f0;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .calendar-day-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .calendar-day {
            background: white;
            min-height: 120px;
            padding: 0.75rem;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
        }

        .calendar-day:hover {
            background: #f8fafc;
        }

        .calendar-day.other-month {
            background: #f8fafc;
            color: #94a3b8;
        }

        .calendar-day.today {
            background: rgba(102, 126, 234, 0.1);
        }

        .day-number {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .calendar-day.today .day-number {
            background: var(--primary-gradient);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .event-item {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s;
        }

        .event-item:hover {
            transform: scale(1.02);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .event-item.task {
            background: #dbeafe;
            color: #1e40af;
        }

        .event-item.meeting {
            background: #fef3c7;
            color: #92400e;
        }

        .event-item.deadline {
            background: #fee2e2;
            color: #dc2626;
        }

        .event-more {
            color: #64748b;
            font-size: 0.7rem;
            margin-top: 0.25rem;
            cursor: pointer;
        }

        .event-more:hover {
            color: var(--primary-color);
        }

        .legend {
            display: flex;
            gap: 1.5rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #64748b;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 6px;
        }

        .legend-color.task {
            background: #dbeafe;
        }

        .legend-color.meeting {
            background: #fef3c7;
        }

        .legend-color.deadline {
            background: #fee2e2;
        }

        /* Event Detail Modal */
        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .event-detail-time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            margin-bottom: 1rem;
        }

        .event-detail-description {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            color: #475569;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .calendar-day {
                min-height: 80px;
                padding: 0.5rem;
            }

            .day-number {
                font-size: 0.85rem;
            }

            .event-item {
                font-size: 0.65rem;
                padding: 0.2rem 0.4rem;
            }

            .calendar-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <nav class="top-navbar">
        <a href="index.php" class="navbar-brand">
            <i class="bi bi-clipboard-check"></i>
            Topic Tracking
        </a>
    </nav>

    <main class="main-content">
        <!-- Header -->
        <div class="calendar-header">
            <div>
                <h1 class="calendar-title">
                    <i class="bi bi-calendar3" style="color: var(--primary-color);"></i>
                    <span id="currentMonth">ธันวาคม 2024</span>
                </h1>
            </div>
            
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <button class="btn-today" onclick="goToToday()">
                    <i class="bi bi-calendar-day me-1"></i>
                    วันนี้
                </button>
                <div class="month-nav">
                    <button class="btn-nav" onclick="prevMonth()">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn-nav" onclick="nextMonth()">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="calendar-header">
            <div class="calendar-filters">
                <div class="filter-chip active" onclick="filterEvents('all')">
                    ทั้งหมด
                </div>
                <div class="filter-chip" onclick="filterEvents('task')">
                    <i class="bi bi-list-task me-1"></i>
                    งาน
                </div>
                <div class="filter-chip" onclick="filterEvents('meeting')">
                    <i class="bi bi-people me-1"></i>
                    ประชุม
                </div>
                <div class="filter-chip" onclick="filterEvents('deadline')">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    เดดไลน์
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="calendar-container">
            <div class="calendar-grid" id="calendarGrid">
                <!-- Calendar will be generated here -->
            </div>

            <!-- Legend -->
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color task"></div>
                    <span>งาน</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color meeting"></div>
                    <span>ประชุม</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color deadline"></div>
                    <span>เดดไลน์</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Event Detail Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle">
                        <i class="bi bi-calendar-event me-2"></i>
                        รายละเอียดกิจกรรม
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="eventModalBody">
                    <!-- Event details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" onclick="viewTaskDetail()">
                        <i class="bi bi-eye me-1"></i>
                        ดูรายละเอียด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mock events data
        const events = [
            { id: 1, title: 'พัฒนาระบบ Login', type: 'task', date: '2024-12-25', time: '09:00', description: 'ออกแบบและพัฒนาระบบ Login ใหม่' },
            { id: 2, title: 'ประชุมทีม Sprint', type: 'meeting', date: '2024-12-26', time: '10:00', description: 'ประชุมวางแผนงาน Sprint ถัดไป' },
            { id: 3, title: 'ส่งเอกสาร API', type: 'deadline', date: '2024-12-27', time: '17:00', description: 'ส่งเอกสาร API Documentation' },
            { id: 4, title: 'Review Code', type: 'task', date: '2024-12-28', time: '14:00', description: 'Review code ของทีม' },
            { id: 5, title: 'ออกแบบ UI', type: 'task', date: '2024-12-29', time: '09:00', description: 'ออกแบบ UI หน้า Dashboard' },
            { id: 6, title: 'ประชุมลูกค้า', type: 'meeting', date: '2024-12-30', time: '15:00', description: 'นำเสนอผลงานให้ลูกค้า' },
            { id: 7, title: 'เดดไลน์โปรเจค', type: 'deadline', date: '2024-12-31', time: '23:59', description: 'ส่งมอบโปรเจค Phase 1' },
            { id: 8, title: 'ทดสอบระบบ', type: 'task', date: '2025-01-02', time: '10:00', description: 'ทดสอบระบบก่อน Deploy' },
            { id: 9, title: 'Meeting Q1 Planning', type: 'meeting', date: '2025-01-03', time: '13:00', description: 'วางแผนงาน Q1 2025' }
        ];

        let currentDate = new Date();
        let currentFilter = 'all';
        let selectedEventId = null;

        const thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 
                           'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const thaiDays = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            document.getElementById('currentMonth').textContent = `${thaiMonths[month]} ${year + 543}`;

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const prevLastDay = new Date(year, month, 0);
            
            const firstDayOfWeek = firstDay.getDay();
            const lastDateOfMonth = lastDay.getDate();
            const prevLastDate = prevLastDay.getDate();

            let html = '';

            // Day headers
            thaiDays.forEach(day => {
                html += `<div class="calendar-day-header">${day}</div>`;
            });

            // Previous month days
            for (let i = firstDayOfWeek - 1; i >= 0; i--) {
                const day = prevLastDate - i;
                html += `<div class="calendar-day other-month">
                    <div class="day-number">${day}</div>
                </div>`;
            }

            // Current month days
            const today = new Date();
            for (let day = 1; day <= lastDateOfMonth; day++) {
                const currentDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isToday = today.getDate() === day && today.getMonth() === month && today.getFullYear() === year;
                
                const dayEvents = getEventsForDate(currentDateStr);
                
                html += `<div class="calendar-day ${isToday ? 'today' : ''}" onclick="selectDate('${currentDateStr}')">
                    <div class="day-number">${day}</div>
                    ${renderEvents(dayEvents, currentDateStr)}
                </div>`;
            }

            // Next month days
            const totalCells = firstDayOfWeek + lastDateOfMonth;
            const nextDays = 7 - (totalCells % 7);
            if (nextDays < 7) {
                for (let day = 1; day <= nextDays; day++) {
                    html += `<div class="calendar-day other-month">
                        <div class="day-number">${day}</div>
                    </div>`;
                }
            }

            document.getElementById('calendarGrid').innerHTML = html;
        }

        function getEventsForDate(dateStr) {
            return events.filter(event => {
                if (currentFilter !== 'all' && event.type !== currentFilter) {
                    return false;
                }
                return event.date === dateStr;
            });
        }

        function renderEvents(dayEvents, dateStr) {
            if (dayEvents.length === 0) return '';

            const maxShow = 3;
            let html = '';
            
            for (let i = 0; i < Math.min(dayEvents.length, maxShow); i++) {
                const event = dayEvents[i];
                html += `<div class="event-item ${event.type}" onclick="event.stopPropagation(); showEventDetail(${event.id})" title="${event.title}">
                    ${event.title}
                </div>`;
            }

            if (dayEvents.length > maxShow) {
                html += `<div class="event-more" onclick="event.stopPropagation(); showDayEvents('${dateStr}')">
                    +${dayEvents.length - maxShow} เพิ่มเติม
                </div>`;
            }

            return html;
        }

        function showEventDetail(eventId) {
            const event = events.find(e => e.id === eventId);
            if (!event) return;

            selectedEventId = eventId;

            const typeNames = {
                'task': 'งาน',
                'meeting': 'ประชุม',
                'deadline': 'เดดไลน์'
            };

            const typeIcons = {
                'task': 'list-task',
                'meeting': 'people',
                'deadline': 'exclamation-triangle'
            };

            document.getElementById('eventModalBody').innerHTML = `
                <h5>${event.title}</h5>
                <div class="event-detail-time">
                    <i class="bi bi-${typeIcons[event.type]}"></i>
                    <span>${typeNames[event.type]}</span>
                    <i class="bi bi-calendar3 ms-3"></i>
                    <span>${formatDate(event.date)}</span>
                    <i class="bi bi-clock ms-3"></i>
                    <span>${event.time} น.</span>
                </div>
                <div class="event-detail-description">
                    ${event.description}
                </div>
            `;

            const modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        }

        function showDayEvents(dateStr) {
            alert(`แสดงกิจกรรมทั้งหมดวันที่ ${formatDate(dateStr)}`);
        }

        function viewTaskDetail() {
            if (selectedEventId) {
                window.location.href = `task_detail.php?taskID=abc${selectedEventId}`;
            }
        }

        function selectDate(dateStr) {
            console.log('Selected date:', dateStr);
        }

        function prevMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        }

        function goToToday() {
            currentDate = new Date();
            renderCalendar();
        }

        function filterEvents(type) {
            currentFilter = type;
            
            document.querySelectorAll('.filter-chip').forEach(chip => {
                chip.classList.remove('active');
            });
            event.target.closest('.filter-chip').classList.add('active');
            
            renderCalendar();
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = date.getDate();
            const month = thaiMonths[date.getMonth()];
            const year = date.getFullYear() + 543;
            return `${day} ${month} ${year}`;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', renderCalendar);
    </script>
</body>
</html>