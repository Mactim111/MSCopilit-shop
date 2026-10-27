import './bootstrap';
import '../css/app.css';

import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

Swiper.use([Navigation, Pagination, Autoplay]);

document.addEventListener('DOMContentLoaded', () => {

    // Длительность показа flash-сообщения в миллисекундах.
    // Чтобы изменить время, поменяйте значение 4000 (например, 6000 = 6 секунд).
    const flashMessageDuration = 4000;

    /*
     * Единая логика flash-сообщений layout: пользователь может закрыть
     * сообщение вручную, а если этого не сделал — оно исчезает автоматически.
     */
    document.querySelectorAll('[data-flash-message]').forEach((message) => {
        setupFlashMessage(message);
    });

    {
        const profileForm = document.querySelector('[data-profile-personal-form]');
        const profileSaveButton = profileForm?.querySelector('[data-profile-save]');
        const profileError = profileForm?.querySelector('[data-profile-form-error]');
        const profileSuccessModal = document.querySelector('[data-profile-success-modal]');
        const profileCalendar = document.querySelector('[data-birthday-calendar]');
        const contactModals = [...document.querySelectorAll('[data-contact-modal]')];
        const birthdayInput = profileForm?.querySelector('[data-birthday-input]');
        const birthdayClearButton = profileForm?.querySelector('[data-birthday-clear]');
        const tabLinks = [...document.querySelectorAll('[data-profile-tab]')];
        const tabPanels = [...document.querySelectorAll('[data-profile-panel]')];
        const breadcrumbLabel = document.querySelector('[data-profile-breadcrumb]');
        const monthLabel = profileCalendar?.querySelector('[data-calendar-month]');
        const yearLabel = profileCalendar?.querySelector('[data-calendar-year]');
        const calendarContent = profileCalendar?.querySelector('[data-calendar-content]');
        const calendarStopIcon = profileCalendar?.querySelector('[data-calendar-stop-icon]');
        let calendarMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
        let calendarMode = 'days';
        let initialProfileState = null;

        const setContactSubmitState = (form, isValid) => {
            const button = form.querySelector('[data-contact-submit]');
            if (!button) return;

            button.disabled = !isValid;
            button.classList.toggle('cursor-not-allowed', !isValid);
            button.classList.toggle('border-[#bdbbbc]', !isValid);
            button.classList.toggle('bg-[#bdbbbc]', !isValid);
            button.classList.toggle('cursor-pointer', isValid);
            button.classList.toggle('border-[#dc092e]', isValid);
            button.classList.toggle('bg-[#dc092e]', isValid);
        };

        const reviewFilterNav = document.querySelector('[data-review-filter-nav]');
        const reviewFilterResults = document.querySelector('[data-review-filter-results]');
        let reviewFilterRequest = null;

        const loadReviewFilter = async (url, updateHistory = false) => {
            if (!reviewFilterNav || !reviewFilterResults) return;

            reviewFilterRequest?.abort();
            const controller = new AbortController();
            reviewFilterRequest = controller;
            reviewFilterNav.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: controller.signal,
                });
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Сервер вернул неожиданный ответ при загрузке отзывов.');
                }

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.message || 'Не удалось загрузить отзывы.');
                }

                reviewFilterResults.innerHTML = payload.html;
                reviewFilterNav.querySelectorAll('[data-review-filter]').forEach(link => {
                    const isActive = link.dataset.reviewFilter === payload.filter;
                    if (isActive) {
                        link.setAttribute('aria-current', 'page');
                    } else {
                        link.removeAttribute('aria-current');
                    }
                    link.classList.add('rounded-[4px]');
                    link.classList.remove('rounded-[10px]');
                    link.classList.toggle('border-black', isActive);
                    link.classList.toggle('bg-white', isActive);
                    link.classList.toggle('font-semibold', isActive);
                    link.classList.toggle('border-[#f4f4f4]', !isActive);
                    link.classList.toggle('bg-[#f4f4f4]', !isActive);
                    link.classList.toggle('hover:border-gray-200', !isActive);
                    link.classList.toggle('hover:bg-white', !isActive);
                    link.classList.toggle('hover:shadow-[0_4px_12px_rgba(0,0,0,0.12)]', !isActive);
                    const count = link.querySelector('[data-review-filter-count]');
                    if (count) {
                        const filterCount = Number(payload.counts[link.dataset.reviewFilter] ?? 0);
                        count.textContent = String(filterCount);
                        link.parentElement?.classList.toggle('hidden', filterCount === 0);
                    }
                });

                if (updateHistory) {
                    window.history.pushState({reviewFilter: payload.filter}, '', url);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    window.alert(error.message || 'Не удалось загрузить отзывы.');
                }
            } finally {
                if (reviewFilterRequest === controller) {
                    reviewFilterNav.removeAttribute('aria-busy');
                }
            }
        };

        reviewFilterNav?.addEventListener('click', event => {
            const link = event.target.closest('[data-review-filter]');
            if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

            event.preventDefault();
            loadReviewFilter(link.href, true);
        });

        if (reviewFilterNav) {
            window.addEventListener('popstate', () => {
                if (window.location.pathname === new URL(reviewFilterNav.querySelector('a').href).pathname) {
                    loadReviewFilter(window.location.href);
                }
            });
        }

        const formatPhone = value => {
            let digits = value.replace(/\D/g, '');
            if (digits.startsWith('375')) digits = digits.slice(3);
            digits = digits.slice(0, 9);
            if (!digits) return '';

            let formatted = `+375 (${digits.slice(0, 2)}`;
            if (digits.length >= 2) formatted += ')';
            if (digits.length > 2) formatted += ` ${digits.slice(2, 5)}`;
            if (digits.length > 5) formatted += `-${digits.slice(5, 7)}`;
            if (digits.length > 7) formatted += `-${digits.slice(7, 9)}`;
            return formatted;
        };

        const phonePattern = /^\+375 \(\d{2}\) \d{3}-\d{2}-\d{2}$/;
        const emailPattern = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;

        contactModals.forEach(modal => {
            const type = modal.dataset.contactModal;
            const form = modal.querySelector(`[data-contact-form="${type}"]`);
            const input = form?.querySelector(`[data-contact-input="${type}"]`);
            if (!form || !input) return;

            const updateContactInputState = () => {
                if (type === 'phone') {
                    const isValid = phonePattern.test(input.value);
                    input.classList.toggle('bg-[#e8f0fe]', isValid);
                    input.classList.toggle('bg-white', !isValid);
                    setContactSubmitState(form, isValid);
                    return;
                }

                const isValid = input.validity.valid && emailPattern.test(input.value);
                input.classList.toggle('bg-[#e8f0fe]', isValid);
                input.classList.toggle('bg-white', !isValid);
                setContactSubmitState(form, isValid);
            };

            if (type === 'phone') {
                input.value = formatPhone(input.value);
                input.addEventListener('input', () => {
                    input.value = formatPhone(input.value);
                    input.setSelectionRange(input.value.length, input.value.length);
                    updateContactInputState();
                });
            }
            input.addEventListener('input', updateContactInputState);
            input.addEventListener('change', updateContactInputState);
            updateContactInputState();

            const closeContactModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                if (!contactModals.some(other => other !== modal && !other.classList.contains('hidden'))) {
                    document.body.classList.remove('overflow-hidden');
                }
            };

            modal.querySelector('[data-contact-modal-close]')?.addEventListener('click', closeContactModal);
            modal.addEventListener('click', event => {
                if (event.target === modal) closeContactModal();
            });
            modal.addEventListener('keydown', event => {
                if (event.key === 'Escape') closeContactModal();
            });

            if (modal.dataset.openOnLoad === 'true') {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
                input.focus();
            }
        });

        document.querySelectorAll('[data-open-contact-modal]').forEach(button => {
            button.addEventListener('click', () => {
                const modal = contactModals.find(item => item.dataset.contactModal === button.dataset.openContactModal);
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
                modal.querySelector('input')?.focus();
            });
        });

        document.addEventListener('keydown', event => {
            if (event.key !== 'Escape') return;
            const activeModal = contactModals.find(modal => !modal.classList.contains('hidden'));
            activeModal?.querySelector('[data-contact-modal-close]')?.click();
        });

        const getProfileState = () => {
            if (!profileForm) return {};

            const data = new FormData(profileForm);
            return {
                name: String(data.get('name') || ''),
                last_name: String(data.get('last_name') || ''),
                gender: String(data.get('gender') || ''),
                birth_date: String(data.get('birth_date') || ''),
            };
        };

        const updateProfileSaveState = () => {
            if (!profileForm || !profileSaveButton) return;

            const isDirty = JSON.stringify(getProfileState()) !== JSON.stringify(initialProfileState);
            profileSaveButton.disabled = !isDirty;
            profileSaveButton.classList.toggle('cursor-not-allowed', !isDirty);
            profileSaveButton.classList.toggle('border-[#bdbbbc]', !isDirty);
            profileSaveButton.classList.toggle('bg-[#bdbbbc]', !isDirty);
            profileSaveButton.classList.toggle('cursor-pointer', isDirty);
            profileSaveButton.classList.toggle('border-[#DC092E]', isDirty);
            profileSaveButton.classList.toggle('bg-[#DC092E]', isDirty);
        };

        const showProfileError = message => {
            if (!profileError) return;
            profileError.textContent = message;
            profileError.classList.toggle('hidden', !message);
        };

        const setProfileTab = (tab, updateUrl = true) => {
            const activePanel = tabPanels.find(panel => panel.dataset.profilePanel === tab)
                || tabPanels.find(panel => panel.dataset.profilePanel === 'personal');
            const activeTab = activePanel?.dataset.profilePanel || 'personal';

            tabPanels.forEach(panel => panel.classList.toggle('hidden', panel !== activePanel));
            tabLinks.forEach(link => {
                const isActive = link.dataset.profileTab === activeTab;
                link.classList.toggle('bg-[#f4f4f4]', isActive);
                link.classList.toggle('before:absolute', isActive);
                link.classList.toggle('before:inset-y-0', isActive);
                link.classList.toggle('before:left-0', isActive);
                link.classList.toggle('before:w-1', isActive);
                link.classList.toggle('before:bg-[#dc092e]', isActive);
                if (isActive) link.setAttribute('aria-current', 'page');
                else link.removeAttribute('aria-current');
            });

            const activeLink = tabLinks.find(link => link.dataset.profileTab === activeTab);
            if (breadcrumbLabel && activeLink) breadcrumbLabel.textContent = activeLink.textContent.trim();

            if (updateUrl) {
                const url = activeTab === 'personal'
                    ? window.location.pathname
                    : `${window.location.pathname}#${activeTab}`;
                window.history.pushState({profileTab: activeTab}, '', url);
            }
        };

        tabLinks.forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                setProfileTab(link.dataset.profileTab);
            });
        });

        if (tabPanels.length) {
            setProfileTab(window.location.hash.slice(1) || 'personal', false);
            window.addEventListener('popstate', () => {
                setProfileTab(window.location.hash.slice(1) || 'personal', false);
            });
        }

        if (profileForm && profileSaveButton) {
            initialProfileState = getProfileState();
            updateProfileSaveState();

            profileForm.querySelectorAll('[data-profile-editable]').forEach(field => {
                const onFieldChange = () => {
                    showProfileError('');
                    const fieldError = profileForm.querySelector(`[data-profile-field-error="${field.name}"]`);
                    if (fieldError) {
                        fieldError.textContent = '';
                        fieldError.classList.add('hidden');
                    }
                    updateProfileSaveState();
                };
                field.addEventListener('input', onFieldChange);
                field.addEventListener('change', onFieldChange);
            });

            profileForm.addEventListener('submit', async event => {
                event.preventDefault();
                if (profileSaveButton.disabled) return;

                showProfileError('');
                profileForm.querySelectorAll('[data-profile-field-error]').forEach(error => {
                    error.textContent = '';
                    error.classList.add('hidden');
                });
                profileSaveButton.disabled = true;

                try {
                    const formData = new FormData(profileForm);
                    const birthday = parseBirthday();
                    formData.set('birth_date', birthday
                        ? [
                            birthday.getFullYear(),
                            String(birthday.getMonth() + 1).padStart(2, '0'),
                            String(birthday.getDate()).padStart(2, '0'),
                        ].join('-')
                        : '');

                    const response = await fetch(profileForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        credentials: 'same-origin',
                        body: formData,
                    });
                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        throw new Error('Сервер вернул неожиданный ответ при сохранении профиля.');
                    }

                    const payload = await response.json();
                    if (!response.ok) {
                        Object.entries(payload.errors || {}).forEach(([field, messages]) => {
                            const fieldError = profileForm.querySelector(`[data-profile-field-error="${field}"]`);
                            if (fieldError) {
                                fieldError.textContent = messages[0];
                                fieldError.classList.remove('hidden');
                            }
                        });
                        throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || 'Не удалось сохранить данные.');
                    }

                    initialProfileState = getProfileState();
                    updateProfileSaveState();
                    profileSuccessModal?.classList.remove('hidden');
                    profileSuccessModal?.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                    profileSuccessModal?.querySelector('[data-profile-success-close]')?.focus();
                } catch (error) {
                    showProfileError(error.message || 'Не удалось сохранить данные профиля.');
                    updateProfileSaveState();
                }
            });

            const closeProfileSuccess = () => {
                profileSuccessModal.classList.add('hidden');
                profileSuccessModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                profileSaveButton.focus();
            };
            profileSuccessModal?.addEventListener('click', event => {
                if (event.target.closest('[data-profile-success-close]') || event.target === profileSuccessModal) {
                    closeProfileSuccess();
                }
            });
            profileSuccessModal?.addEventListener('keydown', event => {
                if (event.key === 'Escape') closeProfileSuccess();
            });
        }

        const parseBirthday = () => {
            const match = birthdayInput?.value.match(/^(\d{2})\.(\d{2})\.(\d{4})$/);
            return match ? new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1])) : null;
        };

        const formatBirthday = date => [
            String(date.getDate()).padStart(2, '0'),
            String(date.getMonth() + 1).padStart(2, '0'),
            date.getFullYear(),
        ].join('.');

        const renderCalendar = () => {
            if (!profileCalendar || !monthLabel || !yearLabel || !calendarContent) return;

            const year = calendarMonth.getFullYear();
            const month = calendarMonth.getMonth();
            const monthNames = ['Янв.', 'Февр.', 'Март', 'Апр.', 'Май', 'Июнь', 'Июль', 'Авг.', 'Сент.', 'Окт.', 'Нояб.', 'Дек.'];
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const currentYear = today.getFullYear();
            const currentMonth = today.getMonth();

            monthLabel.textContent = monthNames[month];
            yearLabel.textContent = String(year);
            monthLabel.setAttribute('aria-label', 'Выбрать месяц');
            yearLabel.setAttribute('aria-label', 'Выбрать год');

            profileCalendar.querySelector('[data-calendar-prev]')?.classList.toggle('invisible', calendarMode !== 'days');
            profileCalendar.querySelector('[data-calendar-next]')?.classList.toggle('invisible', calendarMode !== 'days');
            calendarContent.replaceChildren();

            const addSelectionStyle = (button, isSelected, isCurrent) => {
                if (isSelected) {
                    button.classList.add('border', 'border-[#dc092e]', 'bg-[#dc092e]', 'text-white');
                } else if (isCurrent) {
                    button.classList.add('border', 'border-[#dc092e]', 'text-[#231f20]');
                } else {
                    button.classList.add('text-[#231f20]', 'hover:bg-[#f4f4f4]');
                }
            };

            if (calendarMode === 'months') {
                const monthGrid = document.createElement('div');
                monthGrid.className = 'grid h-full grid-cols-3 grid-rows-4 text-center';

                monthNames.forEach((name, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.calendarMonthOption = String(index);
                    button.textContent = name;
                    button.className = 'mx-auto flex h-[42px] w-full items-center justify-center rounded-[4px] text-[11px] transition-colors';
                    addSelectionStyle(
                        button,
                        index === month,
                        year === currentYear && index === currentMonth
                    );
                    monthGrid.append(button);
                });

                calendarContent.append(monthGrid);
                return;
            }

            if (calendarMode === 'years') {
                const yearGrid = document.createElement('div');
                yearGrid.className = 'relative grid h-full grid-cols-3 content-start overflow-y-auto pb-[110px] text-center';
                yearGrid.dataset.calendarYears = '';

                for (let value = 1900; value <= currentYear; value++) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.calendarYearOption = String(value);
                    button.textContent = String(value);
                    button.className = 'mx-auto flex h-[26px] w-full items-center justify-center rounded-[4px] text-[11px] transition-colors';
                    addSelectionStyle(button, value === year, value === currentYear);
                    yearGrid.append(button);
                }

                const trailingCells = (3 - ((currentYear - 1900 + 1) % 3)) % 3;
                for (let index = 0; index < trailingCells; index++) {
                    const disabledYear = document.createElement('button');
                    disabledYear.type = 'button';
                    disabledYear.disabled = true;
                    disabledYear.setAttribute('aria-label', `${currentYear + index + 1}, будущий год недоступен`);
                    disabledYear.textContent = String(currentYear + index + 1);
                    disabledYear.className = 'relative mx-auto flex h-[26px] w-full cursor-not-allowed items-center justify-center rounded-[4px] border border-[#e5e5e5] bg-[#f6f6f6] text-[11px] text-[#bdbbbc]';
                    if (calendarStopIcon) {
                        const icon = document.createElement('span');
                        icon.className = 'absolute right-1 flex items-center';
                        icon.append(calendarStopIcon.content.cloneNode(true));
                        disabledYear.append(icon);
                    }
                    yearGrid.append(disabledYear);
                }

                calendarContent.append(yearGrid);
                const selectedYear = yearGrid.querySelector(`[data-calendar-year-option="${year}"]`);
                if (selectedYear) {
                    const selectedRect = selectedYear.getBoundingClientRect();
                    const gridRect = yearGrid.getBoundingClientRect();
                    yearGrid.scrollTop += selectedRect.top - gridRect.top
                        - (yearGrid.clientHeight - selectedYear.offsetHeight) / 2;
                }
                return;
            }

            const weekdays = document.createElement('div');
            weekdays.className = 'mb-1 grid h-5 grid-cols-7 text-center text-[10px] font-semibold text-[#231f20]';
            ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс'].forEach(day => {
                const label = document.createElement('span');
                label.textContent = day;
                weekdays.append(label);
            });
            calendarContent.append(weekdays);

            const dayGrid = document.createElement('div');
            dayGrid.className = 'grid grid-cols-7 grid-rows-6 text-center';

            const offset = (new Date(year, month, 1).getDay() + 6) % 7;
            const selectedDate = parseBirthday();

            for (let index = 0; index < 42; index++) {
                const date = new Date(year, month, index - offset + 1);
                date.setHours(0, 0, 0, 0);
                const isCurrentMonth = date.getMonth() === month;
                const isSelected = selectedDate
                    && date.getFullYear() === selectedDate.getFullYear()
                    && date.getMonth() === selectedDate.getMonth()
                    && date.getDate() === selectedDate.getDate();
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.calendarDate = formatBirthday(date);
                button.textContent = String(date.getDate());
                button.disabled = date > today;
                button.className = 'mx-auto flex h-[30px] w-8 items-center justify-center rounded-[4px] text-[11px] transition-colors disabled:cursor-not-allowed disabled:text-[#d6d6d6]';
                if (isSelected) {
                    button.classList.add('border', 'border-[#dc092e]', 'bg-[#dc092e]', 'text-white');
                } else if (date.getTime() === today.getTime()) {
                    button.classList.add('border', 'border-[#dc092e]', 'text-[#231f20]');
                } else if (isCurrentMonth) {
                    button.classList.add('text-[#231f20]', 'hover:bg-[#f4f4f4]');
                } else {
                    button.classList.add('text-[#bdbbbc]', 'hover:bg-[#f4f4f4]');
                }
                dayGrid.append(button);
            }

            calendarContent.append(dayGrid);
        };

        const positionCalendar = () => {
            if (!profileCalendar || !birthdayInput) return;

            const rect = birthdayInput.getBoundingClientRect();
            const calendarHeight = 268;
            const gap = 12;
            const roomBelow = window.innerHeight - rect.bottom;
            const roomAbove = rect.top;
            const placement = roomBelow >= calendarHeight + gap || roomBelow >= roomAbove
                ? 'below'
                : 'above';
            const top = Math.max(8, Math.min(
                placement === 'below' ? rect.bottom + gap : rect.top - calendarHeight - gap,
                window.innerHeight - calendarHeight - 8
            ));
            const left = Math.max(8, Math.min(rect.left + rect.width / 2 - 130.5, window.innerWidth - 269));
            profileCalendar.style.left = `${left}px`;
            profileCalendar.style.top = `${top}px`;

            const topArrow = profileCalendar.querySelector('[data-calendar-arrow="top"]');
            const bottomArrow = profileCalendar.querySelector('[data-calendar-arrow="bottom"]');
            topArrow.classList.toggle('hidden', placement !== 'below');
            bottomArrow.classList.toggle('hidden', placement !== 'above');
            const arrow = placement === 'below' ? topArrow : bottomArrow;
            arrow.style.left = `${Math.max(12, Math.min(rect.left + rect.width / 2 - left, 249))}px`;
        };

        const closeCalendar = () => profileCalendar?.classList.add('hidden');

        if (birthdayInput && profileCalendar) {
            birthdayInput.addEventListener('click', () => {
                const selectedDate = parseBirthday();
                calendarMonth = selectedDate
                    ? new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1)
                    : new Date(new Date().getFullYear(), new Date().getMonth(), 1);
                calendarMode = 'days';
                renderCalendar();
                positionCalendar();
                profileCalendar.classList.remove('hidden');
            });

            profileCalendar.querySelector('[data-calendar-prev]')?.addEventListener('click', () => {
                calendarMonth.setMonth(calendarMonth.getMonth() - 1);
                renderCalendar();
            });
            profileCalendar.querySelector('[data-calendar-next]')?.addEventListener('click', () => {
                calendarMonth.setMonth(calendarMonth.getMonth() + 1);
                renderCalendar();
            });
            monthLabel?.addEventListener('click', () => {
                calendarMode = 'months';
                renderCalendar();
            });
            yearLabel?.addEventListener('click', () => {
                calendarMode = 'years';
                renderCalendar();
            });
            calendarContent?.addEventListener('click', event => {
                event.stopPropagation();
                const monthOption = event.target.closest('[data-calendar-month-option]');
                if (monthOption) {
                    calendarMonth.setMonth(Number(monthOption.dataset.calendarMonthOption));
                    calendarMode = 'days';
                    renderCalendar();
                    return;
                }

                const yearOption = event.target.closest('[data-calendar-year-option]');
                if (yearOption) {
                    calendarMonth.setFullYear(Number(yearOption.dataset.calendarYearOption));
                    calendarMode = 'days';
                    renderCalendar();
                    return;
                }

                const day = event.target.closest('[data-calendar-date]');
                if (!day || day.disabled) return;
                birthdayInput.value = day.dataset.calendarDate;
                birthdayClearButton?.classList.remove('hidden');
                birthdayInput.dispatchEvent(new Event('input', {bubbles: true}));
                birthdayInput.dispatchEvent(new Event('change', {bubbles: true}));
                closeCalendar();
            });
            birthdayClearButton?.addEventListener('click', () => {
                birthdayInput.value = '';
                birthdayClearButton.classList.add('hidden');
                birthdayInput.dispatchEvent(new Event('input', {bubbles: true}));
                birthdayInput.dispatchEvent(new Event('change', {bubbles: true}));
                closeCalendar();
            });
            document.addEventListener('click', event => {
                if (!profileCalendar.contains(event.target) && event.target !== birthdayInput) closeCalendar();
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') closeCalendar();
            });
            window.addEventListener('resize', positionCalendar);
            window.addEventListener('scroll', positionCalendar, true);
        }
    }

    /*
     * AJAX-добавление товара из всех трёх карточек:
     * слайдера, списка вариантов и страницы варианта товара.
     * Маршрут остаётся тем же, поэтому обычная серверная логика корзины
     * продолжает работать и без JavaScript.
     */
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('.js-cart-add-form');

        if (!form) {
            return;
        }

        event.preventDefault();

        const button = form.querySelector('button[type="submit"], button:not([type])');

        if (!button || button.disabled) {
            return;
        }

        const originalText = button.textContent.trim();
        button.disabled = true;
        button.textContent = 'Добавление...';

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            /*
             * Даже при Accept: application/json сервер может вернуть HTML
             * при временной PHP/Laravel-ошибке или redirect. Не пытаемся
             * разбирать такой ответ как JSON, чтобы не показывать пользователю
             * техническую ошибку «Unexpected token '<'».
             */
            const contentType = response.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {
                throw new Error('Сервер вернул неожиданный ответ. Попробуйте повторить действие.');
            }

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'Не удалось добавить товар в корзину');
            }

            updateCartCount(payload.count);
            showFlashMessage(payload.message || 'Товар добавлен в корзину', 'success');

            const link = document.createElement('a');
            link.href = form.dataset.cartUrl || '/cart';
            link.textContent = 'В корзине';
            // Используем классы серверной ссылки «В корзине», а не классы
            // красной кнопки «В корзину», чтобы состояние сразу выглядело одинаково.
            link.className = form.dataset.cartLinkClass || 'block text-center';
            form.replaceWith(link);
        } catch (error) {
            button.disabled = false;
            button.textContent = originalText;
            window.alert(error.message);
        }
    });

    /*
     * AJAX-переключение избранного во всех карточках вариантов.
     * Обработчик находится отдельно от корзины, поэтому работает
     * независимо от предыдущих действий пользователя.
     */
    document.addEventListener('click', async (event) => {
        const showMore = event.target.closest('[data-show-more], [data-favorites-show-more]');

        if (showMore) {
            event.preventDefault();
            event.stopPropagation();

            if (typeof window.loadPage !== 'function') {
                return;
            }

            const wrapper = showMore.closest('#show-more-wrapper, #favorites-show-more-wrapper');

            if (!wrapper) {
                return;
            }

            const currentPage = Number(wrapper.dataset.currentPage);
            const lastPage = Number(wrapper.dataset.lastPage);

            if (currentPage >= lastPage) {
                return;
            }

            const url = new URL(window.location.href);
            url.searchParams.set('page', currentPage + 1);
            await window.loadPage(url.toString());
            return;
        }

        const button = event.target.closest('.favorite-toggle, [data-favorite-remove]');

        if (!button || button.disabled || !button.dataset.favoriteUrl) {
            return;
        }

        event.preventDefault();
        button.disabled = true;

        try {
            const response = await fetch(button.dataset.favoriteUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }

            /*
             * Гостевой запрос проходит через auth middleware и после redirect
             * возвращает HTML страницы входа. Перенаправляем пользователя на
             * неё явно, а HTML других ошибок не пытаемся разбирать как JSON.
             */
            const contentType = response.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {
                if (response.redirected && new URL(response.url).pathname === '/login') {
                    window.location.href = response.url;
                    return;
                }

                throw new Error('Сервер вернул неожиданный ответ. Попробуйте повторить действие.');
            }

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'Не удалось изменить избранное');
            }

            button.innerHTML = payload.icon;
            button.setAttribute('aria-pressed', payload.is_favorite ? 'true' : 'false');
            updateFavoriteCount(payload.count);

            if (button.hasAttribute('data-favorite-remove')) {
                button.closest('[data-favorite-card]')?.remove();
                updateFavoritesPageCount(payload.count);

                /*
                 * На странице избранного сервер также формирует заголовок
                 * со счетчиками подкатегорий. После удаления карточки нужно
                 * заменить этот блок, чтобы обновить счетчики и empty state.
                 */
                if (document.getElementById('favorites-heading')
                    && typeof window.loadFavoritesPage === 'function') {
                    const favoritesUrl = new URL(window.location.href);
                    favoritesUrl.searchParams.delete('page');
                    await window.loadFavoritesPage(favoritesUrl.toString());
                }
            }

            showFlashMessage(payload.message, 'success');
        } catch (error) {
            showFlashMessage(error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });

    function updateCartCount(count) {
        const badge = document.getElementById('cart-count-badge');

        if (!badge) {
            return;
        }

        const normalizedCount = Number(count) || 0;
        badge.textContent = normalizedCount > 99 ? '99+' : normalizedCount;
        badge.classList.toggle('hidden', normalizedCount === 0);
    }

    function updateFavoriteCount(count) {
        const badge = document.getElementById('favorite-count-badge');

        if (!badge) {
            return;
        }

        const normalizedCount = Number(count) || 0;
        badge.textContent = normalizedCount > 99 ? '99+' : normalizedCount;
        badge.classList.toggle('hidden', normalizedCount === 0);
    }

    function updateFavoritesPageCount(count) {
        const counter = document.getElementById('favorites-page-count');

        if (!counter) {
            return;
        }

        counter.textContent = Number(count) || 0;
    }

    /*
     * AJAX-запрос не выполняет redirect и поэтому не может показать
     * session('success') через layout. Создаём такой же flash-блок на странице
     * прямо после успешного ответа сервера.
     */
    function showFlashMessage(message, type = 'success') {
        const container = document.querySelector('#flash-messages > div');

        if (!container) {
            return;
        }

        const removalMessage = /удал|снят/i.test(message);
        const flashType = removalMessage ? 'removal' : type;
        const current = container.querySelector('[data-flash-message]');

        if (current?.querySelector('span')?.textContent === message) {
            setupFlashMessage(current);
            return;
        }

        current?.remove();

        const flash = document.createElement('div');
        const classes = flashType === 'removal' || type === 'error'
            ? 'bg-red-100 text-red-700'
            : 'bg-green-100 text-green-700';

        flash.dataset.flashMessage = '';
        flash.dataset.flashType = flashType;
        flash.className = `pointer-events-auto relative p-3 pr-10 ${classes} rounded text-center shadow-lg transition-opacity duration-300`;
        flash.innerHTML = `
            <span></span>
            <button type="button"
                    data-dismiss-flash
                    aria-label="Закрыть сообщение"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xl leading-none opacity-60 hover:opacity-100">
                &times;
            </button>
        `;
        flash.querySelector('span').textContent = message;
        container.replaceChildren(flash);
        setupFlashMessage(flash);
    }

    function setupFlashMessage(flash) {
        const close = () => {
            if (!flash.isConnected) {
                return;
            }

            flash.classList.add('opacity-0');
            window.setTimeout(() => flash.remove(), 300);
        };

        flash.querySelector('[data-dismiss-flash]').addEventListener('click', close);
        window.clearTimeout(Number(flash.dataset.timeoutId));
        flash.dataset.timeoutId = String(window.setTimeout(close, flashMessageDuration));
    }

    // Эти функции используются страницей избранного для синхронизации
    // счетчиков после AJAX-фильтрации и удаления подкатегории.
    window.updateFavoriteCount = updateFavoriteCount;
    window.updateFavoritesPageCount = updateFavoritesPageCount;
    window.showFlashMessage = showFlashMessage;

    // Универсальная модалка создания отзыва открывается и из заказа, и со страницы варианта.
    const reviewModal = document.querySelector('[data-review-create-modal]');

    if (reviewModal) {
        const formPanel = reviewModal.querySelector('[data-review-form-panel]');
        const formState = reviewModal.querySelector('[data-review-form-state]');
        const thanksPanel = reviewModal.querySelector('[data-review-thanks-panel]');
        const form = reviewModal.querySelector('[data-review-create-form]');
        const ratingInput = reviewModal.querySelector('[data-review-rating]');
        const commentInput = reviewModal.querySelector('[data-review-comment]');
        const submitButton = reviewModal.querySelector('[data-review-submit]');
        const loadingOverlay = reviewModal.querySelector('[data-review-loading]');
        const errorMessage = reviewModal.querySelector('[data-review-form-error]');
        const starButtons = [...reviewModal.querySelectorAll('[data-rating-star]')];
        let selectedRating = 0;
        let isSubmitting = false;
        let wasSubmitted = false;
        let previousFocus = null;

        const showFormError = message => {
            errorMessage.textContent = message;
            errorMessage.classList.toggle('hidden', !message);
        };

        const updateSubmitState = () => {
            const isValid = selectedRating > 0 && commentInput.value.trim().length > 0;
            submitButton.disabled = !isValid || isSubmitting;
            submitButton.classList.toggle('cursor-not-allowed', submitButton.disabled);
            submitButton.classList.toggle('border-[#bdbbbc]', submitButton.disabled);
            submitButton.classList.toggle('bg-[#bdbbbc]', submitButton.disabled);
            submitButton.classList.toggle('cursor-pointer', !submitButton.disabled);
            submitButton.classList.toggle('border-[#DC092E]', !submitButton.disabled);
            submitButton.classList.toggle('bg-[#DC092E]', !submitButton.disabled);
        };

        const setRating = rating => {
            selectedRating = Math.max(0, Math.min(5, rating));
            ratingInput.value = selectedRating ? String(selectedRating) : '';

            starButtons.forEach((button, index) => {
                const active = index < selectedRating;
                button.setAttribute('aria-checked', String(index + 1 === selectedRating));

                const star = button.querySelector('svg');
                star.classList.toggle('fill-white', !active);
                star.classList.toggle('stroke-[#DC092E]', !active);
                star.classList.toggle('fill-[#ffb000]', active);
                star.classList.toggle('stroke-[#ffb000]', active);
            });

            updateSubmitState();
        };

        const clearMediaPreview = input => {
            const slot = input.closest('[data-media-slot]').parentElement;
            const preview = slot.querySelector('[data-media-preview]');
            const placeholder = slot.querySelector('[data-media-placeholder]');
            const removeButton = slot.querySelector('[data-media-remove]');

            if (input.dataset.previewUrl) {
                URL.revokeObjectURL(input.dataset.previewUrl);
                delete input.dataset.previewUrl;
            }

            input.value = '';
            preview.replaceChildren();
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            removeButton.textContent = '+';
            removeButton.setAttribute(
                'aria-label',
                input.dataset.mediaKind === 'video' ? 'Добавить видео' : 'Добавить фотографию'
            );
        };

        const resetForm = () => {
            form.reset();
            showFormError('');
            setRating(0);
            reviewModal.querySelectorAll('[data-media-input]').forEach(clearMediaPreview);
            submitButton.disabled = true;
            isSubmitting = false;
            wasSubmitted = false;
            loadingOverlay.classList.add('hidden');
            loadingOverlay.classList.remove('flex');
            formPanel.setAttribute('aria-busy', 'false');
        };

        const closeModal = () => {
            if (isSubmitting) return;

            reviewModal.classList.add('hidden');
            reviewModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');

            if (wasSubmitted) {
                window.location.reload();
                return;
            }

            resetForm();
            formPanel.classList.remove('hidden');
            formState.classList.remove('hidden');
            thanksPanel.classList.add('hidden');
            thanksPanel.classList.remove('flex');
            reviewModal.setAttribute('aria-labelledby', 'review-create-title');
            previousFocus?.focus();
        };

        const openModal = trigger => {
            if (!form || !formState || !thanksPanel || !formPanel || !submitButton) return;

            resetForm();
            previousFocus = trigger;
            form.action = trigger.dataset.formAction;

            const productTitle = trigger.dataset.productTitle || '';
            const productImage = trigger.dataset.productImage || '';
            const productTitleElement = reviewModal.querySelector('[data-review-product-title]');
            const productImageElement = reviewModal.querySelector('[data-review-product-image]');
            productTitleElement.textContent = productTitle;
            productImageElement.src = productImage;
            productImageElement.alt = productTitle;
            productImageElement.classList.toggle('hidden', !productImage);

            formState.classList.remove('hidden');
            formPanel.classList.remove('hidden');
            thanksPanel.classList.add('hidden');
            thanksPanel.classList.remove('flex');
            reviewModal.setAttribute('aria-labelledby', 'review-create-title');
            reviewModal.classList.remove('hidden');
            reviewModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            starButtons[0]?.focus();
        };

        document.addEventListener('click', event => {
            const trigger = event.target.closest('[data-review-form="create"]');
            if (trigger) {
                event.preventDefault();
                openModal(trigger);
                return;
            }

            if (event.target.closest('[data-review-modal-close], [data-review-thanks-close]')) {
                closeModal();
                return;
            }

            if (event.target === reviewModal) {
                closeModal();
                return;
            }

            const starButton = event.target.closest('[data-rating-star]');
            if (starButton) {
                const rating = Number(starButton.dataset.ratingStar);
                setRating(rating);
            }

            const removeButton = event.target.closest('[data-media-remove]');
            if (removeButton) {
                const slot = removeButton.closest('[data-media-slot]').parentElement;
                const input = slot.querySelector('[data-media-input]');
                if (input.files.length) {
                    clearMediaPreview(input);
                    showFormError('');
                    updateSubmitState();
                } else {
                    input.click();
                }
            }
        });

        reviewModal.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                closeModal();
                return;
            }

            const starButton = event.target.closest('[data-rating-star]');
            if (!starButton || !['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();

            const current = Number(starButton.dataset.ratingStar);
            const next = event.key === 'Home'
                ? 1
                : event.key === 'End'
                    ? 5
                    : Math.max(1, Math.min(5, current + (event.key === 'ArrowRight' ? 1 : -1)));
            starButtons[next - 1].focus();
            setRating(next);
        });

        commentInput.addEventListener('input', updateSubmitState);

        reviewModal.querySelectorAll('[data-media-input]').forEach(input => {
            input.addEventListener('change', () => {
                const file = input.files[0];
                if (!file) return;

                const isVideo = input.dataset.mediaKind === 'video';
                const extension = file.name.split('.').pop().toLowerCase();
                const acceptedExtensions = isVideo
                    ? ['avi', 'mp4', 'hevc']
                    : ['jpg', 'jpeg', 'png'];
                const maxSize = (isVideo ? 100 : 5) * 1024 * 1024;

                if (!acceptedExtensions.includes(extension) || file.size > maxSize) {
                    clearMediaPreview(input);
                    showFormError(isVideo
                        ? 'Выберите видео в формате AVI, MP4 или HEVC размером не более 100 МБ.'
                        : 'Выберите изображение JPG или PNG размером не более 5 МБ.');
                    return;
                }

                showFormError('');
                const slot = input.closest('[data-media-slot]').parentElement;
                const preview = slot.querySelector('[data-media-preview]');
                const placeholder = slot.querySelector('[data-media-placeholder]');
                const removeButton = slot.querySelector('[data-media-remove]');
                if (input.dataset.previewUrl) {
                    URL.revokeObjectURL(input.dataset.previewUrl);
                }
                const previewUrl = URL.createObjectURL(file);
                input.dataset.previewUrl = previewUrl;

                const mediaPreview = document.createElement(isVideo ? 'video' : 'img');
                mediaPreview.className = 'h-full w-full object-cover';
                mediaPreview.src = previewUrl;
                if (isVideo) {
                    mediaPreview.muted = true;
                    mediaPreview.playsInline = true;
                } else {
                    mediaPreview.alt = 'Предпросмотр фотографии';
                }

                preview.replaceChildren(mediaPreview);
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                removeButton.textContent = '−';
                removeButton.setAttribute('aria-label', isVideo ? 'Удалить видео' : 'Удалить фотографию');
            });
        });

        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (submitButton.disabled || isSubmitting) return;

            showFormError('');
            isSubmitting = true;
            submitButton.disabled = true;
            loadingOverlay.classList.remove('hidden');
            loadingOverlay.classList.add('flex');
            formPanel.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin',
                    body: new FormData(form),
                });
                const contentType = response.headers.get('content-type') || '';

                if (!contentType.includes('application/json')) {
                    throw new Error('Сервер вернул неожиданный ответ. Проверьте размер файлов и попробуйте ещё раз.');
                }

                const payload = await response.json();
                if (!response.ok) {
                    const validationError = Object.values(payload.errors || {}).flat()[0];
                    throw new Error(validationError || payload.message || 'Не удалось отправить отзыв.');
                }

                isSubmitting = false;
                wasSubmitted = true;
                loadingOverlay.classList.add('hidden');
                loadingOverlay.classList.remove('flex');
                formPanel.classList.add('hidden');
                formState.classList.add('hidden');
                thanksPanel.classList.remove('hidden');
                thanksPanel.classList.add('flex');
                reviewModal.setAttribute('aria-labelledby', 'review-thanks-title');
                thanksPanel.querySelector('h2').id = 'review-thanks-title';
                thanksPanel.querySelector('[data-review-thanks-close]').focus();
            } catch (error) {
                showFormError(error.message || 'Не удалось отправить отзыв.');
                isSubmitting = false;
                loadingOverlay.classList.add('hidden');
                loadingOverlay.classList.remove('flex');
                formPanel.setAttribute('aria-busy', 'false');
                updateSubmitState();
            }
        });
    }

    const reviewAdditionModal = document.querySelector('[data-review-addition-modal]');
    const reviewAdditionForm = reviewAdditionModal?.querySelector('[data-review-addition-form]');
    const reviewAdditionInput = reviewAdditionModal?.querySelector('[data-review-addition-input]');
    const reviewAdditionSubmit = reviewAdditionModal?.querySelector('[data-review-addition-submit]');
    const reviewResultModal = document.querySelector('[data-review-action-result-modal]');
    const reviewResultPanel = reviewResultModal?.querySelector('[data-review-action-result-panel]');
    const reviewResultMessage = reviewResultModal?.querySelector('[data-review-result-message]');
    let reviewAdditionSubmitting = false;
    let reviewResultReload = false;
    let reviewActionTrigger = null;

    const syncReviewModalScrollLock = () => {
        const isAnyReviewModalOpen = [reviewModal, reviewAdditionModal, reviewResultModal]
            .some(modal => modal && !modal.classList.contains('hidden'));
        document.body.classList.toggle('overflow-hidden', isAnyReviewModalOpen);
    };

    const openReviewResult = (message, { isError = false, reload = false } = {}) => {
        if (!reviewResultModal || !reviewResultPanel || !reviewResultMessage) return;

        reviewResultMessage.textContent = message;
        reviewResultPanel.classList.toggle('h-[264px]', !isError);
        reviewResultPanel.classList.toggle('w-[290px]', !isError);
        reviewResultPanel.classList.toggle('h-[164px]', isError);
        reviewResultPanel.classList.toggle('w-[422px]', isError);
        reviewResultMessage.classList.toggle('whitespace-nowrap', !isError);
        reviewResultReload = reload;
        reviewResultModal.classList.remove('hidden');
        reviewResultModal.classList.add('flex');
        syncReviewModalScrollLock();
        reviewResultModal.querySelector('[data-review-result-close]')?.focus();
    };

    const closeReviewAddition = () => {
        if (!reviewAdditionModal || reviewAdditionSubmitting) return;

        reviewAdditionModal.classList.add('hidden');
        reviewAdditionModal.classList.remove('flex');
        reviewAdditionForm?.reset();
        if (reviewAdditionSubmit) {
            reviewAdditionSubmit.disabled = true;
            reviewAdditionSubmit.classList.add('cursor-not-allowed', 'border-[#bdbbbc]', 'bg-[#bdbbbc]');
            reviewAdditionSubmit.classList.remove('cursor-pointer', 'border-[#DC092E]', 'bg-[#DC092E]');
        }
        syncReviewModalScrollLock();
        reviewActionTrigger?.focus();
    };

    const closeReviewResult = () => {
        if (!reviewResultModal) return;

        reviewResultModal.classList.add('hidden');
        reviewResultModal.classList.remove('flex');
        syncReviewModalScrollLock();

        if (reviewResultReload) {
            window.location.reload();
            return;
        }

        reviewActionTrigger?.focus();
    };

    const updateReviewAdditionSubmit = () => {
        if (!reviewAdditionInput || !reviewAdditionSubmit) return;

        const isDisabled = !reviewAdditionInput.value.trim() || reviewAdditionSubmitting;
        reviewAdditionSubmit.disabled = isDisabled;
        reviewAdditionSubmit.classList.toggle('cursor-not-allowed', isDisabled);
        reviewAdditionSubmit.classList.toggle('border-[#bdbbbc]', isDisabled);
        reviewAdditionSubmit.classList.toggle('bg-[#bdbbbc]', isDisabled);
        reviewAdditionSubmit.classList.toggle('cursor-pointer', !isDisabled);
        reviewAdditionSubmit.classList.toggle('border-[#DC092E]', !isDisabled);
        reviewAdditionSubmit.classList.toggle('bg-[#DC092E]', !isDisabled);
    };

    if (reviewAdditionModal && reviewAdditionForm && reviewAdditionInput && reviewAdditionSubmit) {
        document.addEventListener('click', event => {
            const trigger = event.target.closest('[data-review-form="addition"]');
            if (trigger) {
                event.preventDefault();
                reviewActionTrigger = trigger;
                reviewAdditionForm.reset();
                reviewAdditionForm.action = trigger.dataset.formAction;
                updateReviewAdditionSubmit();
                reviewAdditionModal.classList.remove('hidden');
                reviewAdditionModal.classList.add('flex');
                syncReviewModalScrollLock();
                reviewAdditionInput.focus();
                return;
            }

            if (event.target.closest('[data-review-addition-close]') || event.target === reviewAdditionModal) {
                closeReviewAddition();
                return;
            }

            if (event.target.closest('[data-review-result-close]') || event.target === reviewResultModal) {
                closeReviewResult();
            }
        });

        reviewAdditionInput.addEventListener('input', updateReviewAdditionSubmit);

        reviewAdditionModal.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeReviewAddition();
        });

        reviewResultModal?.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeReviewResult();
        });

        reviewAdditionForm.addEventListener('submit', async event => {
            event.preventDefault();
            if (reviewAdditionSubmit.disabled || reviewAdditionSubmitting) return;

            reviewAdditionSubmitting = true;
            updateReviewAdditionSubmit();

            try {
                const response = await fetch(reviewAdditionForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin',
                    body: new FormData(reviewAdditionForm),
                });
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Сервер вернул неожиданный ответ.');
                }

                const payload = await response.json();
                if (!response.ok) {
                    const validationError = Object.values(payload.errors || {}).flat()[0];
                    throw new Error(validationError || payload.message || 'Не удалось дополнить отзыв.');
                }

                reviewAdditionSubmitting = false;
                reviewAdditionModal.classList.add('hidden');
                reviewAdditionModal.classList.remove('flex');
                syncReviewModalScrollLock();
                openReviewResult('Данные успешно обновлены', { reload: true });
            } catch (error) {
                reviewAdditionSubmitting = false;
                updateReviewAdditionSubmit();
                reviewAdditionModal.classList.add('hidden');
                reviewAdditionModal.classList.remove('flex');
                syncReviewModalScrollLock();
                openReviewResult('Ошибка при добавлении комментария к отзыву', { isError: true });
            }
        });

        document.addEventListener('submit', async event => {
            const deleteForm = event.target.closest('[data-review-delete-form]');
            if (!deleteForm) return;

            event.preventDefault();
            const deleteButton = deleteForm.querySelector('button[type="submit"]');
            if (deleteButton?.disabled) return;

            if (deleteButton) deleteButton.disabled = true;

            try {
                const response = await fetch(deleteForm.action, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': deleteForm.querySelector('input[name="_token"]')?.value
                            || document.querySelector('meta[name="csrf-token"]')?.content
                            || '',
                    },
                    credentials: 'same-origin',
                });
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error('Сервер вернул неожиданный ответ.');
                }

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.message || 'Не удалось удалить отзыв.');
                }

                reviewActionTrigger = deleteButton;
                openReviewResult('Данные успешно обновлены', { reload: true });
            } catch (error) {
                if (deleteButton) deleteButton.disabled = false;
                reviewActionTrigger = deleteButton;
                openReviewResult(error.message || 'Не удалось удалить отзыв.', { isError: true });
            }
        });
    }

// -------------------------------------------------------------------------
    // ИНИЦИАЛИЗАЦИЯ ВСЕХ SWIPER-СЛАЙДЕРОВ
    // -------------------------------------------------------------------------
    // Этот блок автоматически находит все элементы с классом .js-swiper 
    // и настраивает их на основе data-атрибутов в верстке.
    document.querySelectorAll('.js-swiper').forEach(swiperEl => {

        // Ищем элементы управления внутри родительского контейнера, 
        // чтобы слайдеры на одной странице не конфликтовали друг с другом.
        const container = swiperEl.parentElement;
        const next = container.querySelector('.js-swiper-next');
        const prev = container.querySelector('.js-swiper-prev');
        const pagination = container.querySelector('.js-swiper-pagination');

        new Swiper(swiperEl, {
            // Подключаем необходимые модули
            modules: [Navigation, Pagination, Autoplay],

            // Количество отображаемых слайдов: берем из data-slides или ставим 8 по умолчанию
            slidesPerView: swiperEl.dataset.slides === 'auto' ? 'auto' : Number(swiperEl.dataset.slides ?? 8),
            
            // Количество пролистываемых слайдов за один раз (секциями)
            slidesPerGroup: Number(swiperEl.dataset.group ?? 1),
            
            // Расстояние между слайдами (в пикселях)
            spaceBetween: Number(swiperEl.dataset.space ?? 12.5),
            
            // Бесконечный цикл прокрутки
            loop: swiperEl.dataset.loop === 'true',
            
            // Изменение курсора на "руку" при наведении
            grabCursor: swiperEl.dataset.grab === 'true',

            // Улучшение отрисовки: Swiper будет следить за прогрессом видимости слайдов
            watchSlidesProgress: true, 
            
            // Если слайдов меньше, чем нужно для заполнения ряда, они будут отцентрированы
            centerInsufficientSlides: true, 

            // Настройка автопролистывания
            autoplay: swiperEl.dataset.autoplay === 'true'
                ? { 
                    delay: 4000, // Установлен интервал 4 секунды (4000мс)
                    disableOnInteraction: false // Не останавливать автоплей после кликов пользователя
                  }
                : false,

            // Настройка стрелок "Вперед/Назад"
            navigation: swiperEl.dataset.navigation === 'true'
                ? { nextEl: next, prevEl: prev }
                : false,

            // Настройка пагинации (полосок под слайдером)
            pagination: swiperEl.dataset.pagination === 'true'
                ? { 
                    el: pagination, 
                    clickable: true // Позволяет кликать по полоскам для перехода
                  }
                : false,
        });
    });

    // -----------------------------
    // КАСТОМНОЕ МОДАЛЬНОЕ ОКНО ДЛЯ ЗАКРЫТИЯ TOP-BANNER
    // -----------------------------
    const banner = document.getElementById('top-banner');
    const closeBtn = document.getElementById('banner-top-close');
    const modal = document.getElementById('banner-confirm');
    const hideBtn = document.getElementById('banner-hide');
    const cancelBtn = document.getElementById('banner-cancel');

    // Если баннера нет — ничего не делаем
    if (banner && closeBtn && modal) {

        // Открыть модалку
        closeBtn.addEventListener('click', () => {

            const rect = closeBtn.getBoundingClientRect();
            const modalWidth = 278; // ширина окна

            // ПРАВАЯ ГРАНИЦА МОДАЛКИ = ПРАВАЯ ГРАНИЦА КНОПКИ
            modal.style.left = (rect.right - modalWidth - 10) + "px";

            // МОДАЛКА ПОД КНОПКОЙ, СМЕЩЕНИЕ 20px
            modal.style.top = (rect.bottom + 10) + "px";

            modal.classList.remove('hidden');
        });

        // Скрыть баннер
        hideBtn.addEventListener('click', () => {
            banner.style.transition = 'opacity 0.3s ease';
            banner.style.opacity = '0';
            setTimeout(() => banner.remove(), 300);
            modal.style.display = 'none';
        });

        // Отмена
        cancelBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

});
