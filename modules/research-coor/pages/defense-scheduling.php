<section class="defense-scheduling-page">
    <div class="module-header defense-module-header">
        <div>
            <h1 id="defenseSchedulingTitle">Defense Scheduling</h1>
            <p>Assign research defense schedules and manage them in the calendar.</p>
        </div>
        <a class="defense-calendar-link" href="?page=events-management" data-page="events-management">
            <i class="fas fa-calendar-days" aria-hidden="true"></i>
            View Events &amp; Calendar
        </a>
    </div>

    <div id="defenseScheduleNotice" class="defense-notice" role="status" aria-live="polite" hidden></div>

    <div class="defense-layout">
        <form id="defenseScheduleForm" class="defense-card">
            <div class="defense-card-heading">
                <span class="defense-heading-icon"><i class="fas fa-calendar-plus" aria-hidden="true"></i></span>
                <div>
                    <h2>Assign Defense Schedule</h2>
                    <p>Required details are marked with an asterisk.</p>
                </div>
            </div>

            <div class="defense-form-grid">
                <label class="defense-field defense-field-wide">
                    <span>Research Project Title <b>*</b></span>
                    <input type="text" name="project_title" maxlength="200" required placeholder="Enter the approved research title">
                </label>
                <label class="defense-field">
                    <span>Defense Stage <b>*</b></span>
                    <select name="defense_stage" required>
                        <option value="">Select defense stage</option>
                        <option value="Proposal Defense">Proposal Defense</option>
                        <option value="Pre-oral Defense">Pre-oral Defense</option>
                        <option value="Final Defense">Final Defense</option>
                        <option value="Re-defense">Re-defense</option>
                    </select>
                </label>
                <label class="defense-field">
                    <span>Candidate / Researcher <b>*</b></span>
                    <input type="text" name="candidate" maxlength="120" required placeholder="Student or group representative">
                </label>
                <label class="defense-field">
                    <span>Research Adviser <b>*</b></span>
                    <input type="text" name="adviser" maxlength="120" required placeholder="Adviser's full name">
                </label>
                <label class="defense-field">
                    <span>Panel Members</span>
                    <input type="text" name="panel_members" maxlength="200" placeholder="Separate names with commas">
                </label>
                <label class="defense-field">
                    <span>Defense Date <b>*</b></span>
                    <input type="date" name="defense_date" required>
                </label>
                <label class="defense-field">
                    <span>Meeting Mode <b>*</b></span>
                    <select name="meeting_mode" required>
                        <option value="In-person">In-person</option>
                        <option value="Online">Online</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </label>
                <label class="defense-field">
                    <span>Start Time <b>*</b></span>
                    <input type="time" name="start_time" required>
                </label>
                <label class="defense-field">
                    <span>End Time <b>*</b></span>
                    <input type="time" name="end_time" required>
                </label>
                <label class="defense-field defense-field-wide">
                    <span>Venue / Meeting Location <b>*</b></span>
                    <input type="text" name="venue" maxlength="255" required placeholder="Room, building, or online platform">
                </label>
                <label class="defense-field defense-field-wide">
                    <span>Meeting Link</span>
                    <input type="url" name="meeting_link" maxlength="255" placeholder="https://... (if applicable)">
                </label>
                <label class="defense-field defense-field-wide">
                    <span>Additional Instructions</span>
                    <textarea name="notes" rows="3" maxlength="1500" placeholder="Add preparation notes or reminders for the candidate and panel"></textarea>
                </label>
            </div>

            <div class="defense-form-actions">
                <button class="defense-submit" type="submit">
                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                    Assign &amp; Add to Calendar
                </button>
                <button class="defense-reset" type="reset">Clear Form</button>
            </div>
        </form>

        <aside class="defense-calendar-card" aria-label="Defense calendar">
            <div class="defense-calendar-heading">
                <div>
                    <span class="defense-calendar-eyebrow"><i class="fas fa-link" aria-hidden="true"></i> Calendar Connection</span>
                    <h2>Defense Calendar</h2>
                </div>
                <a href="?page=events-management" data-page="events-management" aria-label="Open Events and Calendar">
                    <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                </a>
            </div>
            <div class="defense-calendar-controls">
                <button type="button" id="defenseCalendarPrevious" aria-label="Previous month"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                <strong id="defenseCalendarMonth" aria-live="polite">Loading calendar...</strong>
                <button type="button" id="defenseCalendarNext" aria-label="Next month"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
            </div>
            <div class="defense-calendar-weekdays" aria-hidden="true">
                <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
            </div>
            <div class="defense-calendar-days" id="defenseCalendarDays" role="grid" aria-label="Monthly defense calendar">
                <p class="defense-calendar-loading">Loading calendar...</p>
            </div>
            <div class="defense-calendar-legend"><span></span> Scheduled defense</div>
            <section class="defense-calendar-agenda" aria-live="polite">
                <h3 id="defenseCalendarSelectedDate">Upcoming Defenses</h3>
                <div id="defenseCalendarAgenda">
                    <p class="defense-calendar-empty">Loading defense events...</p>
                </div>
            </section>
            <div class="defense-info-note">
                <i class="fas fa-circle-check" aria-hidden="true"></i>
                <span>New schedules appear here and in Events &amp; Calendar.</span>
            </div>
        </aside>
    </div>

    <section class="defense-card defense-list-card">
        <div class="defense-card-heading defense-list-heading">
            <span class="defense-heading-icon"><i class="fas fa-list-check" aria-hidden="true"></i></span>
            <div>
                <h2>Scheduled Research Defenses</h2>
                <p>Assignments saved to the shared calendar appear here.</p>
            </div>
            <button type="button" class="defense-refresh" id="refreshDefenseSchedules" aria-label="Refresh defense schedule list">
                <i class="fas fa-rotate" aria-hidden="true"></i>
            </button>
        </div>
        <div class="defense-table-wrap">
            <table class="defense-table">
                <thead>
                    <tr>
                        <th>Research Project / Stage</th>
                        <th>Candidate</th>
                        <th>Date &amp; Time</th>
                        <th>Venue</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="defenseScheduleRows">
                    <tr><td class="defense-table-message" colspan="5">Loading scheduled defenses...</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</section>
