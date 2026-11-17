/**
 * Interactive Scripts untuk Halaman Akademi Air PHP Standalone
 * Smart Water Kota Payakumbuh
 */

const ACADEMY_COURSES = Array.isArray(window.SWC_COURSES) ? window.SWC_COURSES : [];
let academyProgress = window.SWC_ACADEMY_PROGRESS || {
  points: 380,
  completedLessons: [],
  completedQuizzes: {},
  badges: [],
};

let selectedCategory = 'all';
let searchQuery = '';

// Reader Modal State
let activeReaderCourse = null;
let activeReaderLessonIdx = 0;

// Quiz Page State
let activeQuizCourse = null;
let quizIndex = 0;
let quizSelectedAnswers = {};
let isQuizConfirmed = false;
let quizStreak = 0;
let quizMaxStreak = 0;
let quizPointsEarned = 0;

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// ---------------------------------------------------------------------------
// 1. HEADER MOBILE MENU & NAVIGATION MODAL
// ---------------------------------------------------------------------------
function toggleMobileMenu() {
  const drawer = document.getElementById('mobileMenuDrawer');
  const openIcon = document.getElementById('mobileMenuIconOpen');
  const closeIcon = document.getElementById('mobileMenuIconClose');
  const btn = document.getElementById('mobileMenuToggleBtn');
  if (!drawer) return;
  const isHidden = drawer.classList.contains('hidden');
  if (isHidden) {
    drawer.classList.remove('hidden');
    if (openIcon) openIcon.classList.add('hidden');
    if (closeIcon) closeIcon.classList.remove('hidden');
    if (btn) btn.setAttribute('aria-expanded', 'true');
  } else {
    closeMobileMenu();
  }
}

function closeMobileMenu() {
  const drawer = document.getElementById('mobileMenuDrawer');
  const openIcon = document.getElementById('mobileMenuIconOpen');
  const closeIcon = document.getElementById('mobileMenuIconClose');
  const btn = document.getElementById('mobileMenuToggleBtn');
  if (drawer) drawer.classList.add('hidden');
  if (openIcon) openIcon.classList.remove('hidden');
  if (closeIcon) closeIcon.classList.add('hidden');
  if (btn) btn.setAttribute('aria-expanded', 'false');
}

const ROUTE_MAP = {
  beranda: '/beranda',
  layanan: '/pusat-layanan',
  'pusat-layanan': '/pusat-layanan',
  akademi: '/akademi-air',
  'akademi-air': '/akademi-air',
  tantangan: '/tantangan',
  tentang: '/tentang',
  profil: '/user',
  user: '/user',
  admin: '/admin',
  auth: '/login',
  login: '/login',
};

function getAppBaseUrl(path) {
  const pathname = window.location.pathname;
  if (pathname.includes('/smart-water')) {
    return '/smart-water' + path;
  }
  return path;
}

function handleAcademyNav(tab) {
  if (tab === 'akademi') {
    closeQuizPageView();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  const target = ROUTE_MAP[tab] || '/' + tab;
  window.location.href = getAppBaseUrl(target);
}

function openAuthNoticeModal() {
  window.location.href = getAppBaseUrl('/login');
}

function openNavModal(title, desc, href) {
  const modal = document.getElementById('academy-nav-modal');
  if (modal) modal.classList.add('hidden');
}

function closeNavModal() {
  const modal = document.getElementById('academy-nav-modal');
  if (modal) modal.classList.add('hidden');
}

// ---------------------------------------------------------------------------
// 2. COURSE CATALOG FILTERING & DYNAMIC PROGRESS SYNC
// ---------------------------------------------------------------------------
function selectCategoryFilter(catId) {
  selectedCategory = catId;
  document.querySelectorAll('.academy-cat-btn').forEach((btn) => {
    const isThis = btn.getAttribute('data-cat-id') === catId;
    btn.className = isThis
      ? 'academy-cat-btn px-3 py-1.5 rounded-lg whitespace-nowrap shrink-0 transition-colors font-medium cursor-pointer bg-slate-900 text-white shadow-xs'
      : 'academy-cat-btn px-3 py-1.5 rounded-lg whitespace-nowrap shrink-0 transition-colors font-medium cursor-pointer bg-white text-slate-600 hover:text-slate-900 border border-slate-200';
  });
  applyAcademyFilters();
}

function filterAcademySearch(query) {
  searchQuery = (query || '').trim().toLowerCase();
  applyAcademyFilters();
}

function applyAcademyFilters() {
  const cards = document.querySelectorAll('[data-course-card]');
  let visible = 0;

  cards.forEach((card) => {
    const cat = card.getAttribute('data-course-cat') || '';
    const title = (card.getAttribute('data-course-title') || '').toLowerCase();
    const summary = (card.getAttribute('data-course-summary') || '').toLowerCase();

    const matchCat = selectedCategory === 'all' || cat === selectedCategory;
    const matchQuery = !searchQuery || title.includes(searchQuery) || summary.includes(searchQuery);

    if (matchCat && matchQuery) {
      card.classList.remove('hidden');
      visible++;
    } else {
      card.classList.add('hidden');
    }
  });

  const emptyEl = document.getElementById('academy-empty-state');
  if (emptyEl) {
    if (visible === 0) emptyEl.classList.remove('hidden');
    else emptyEl.classList.add('hidden');
  }
}

function syncCourseCardsUI() {
  const ptsEl = document.getElementById('header-points-value');
  if (ptsEl) ptsEl.textContent = String(academyProgress.points || 0);

  ACADEMY_COURSES.forEach((course) => {
    const card = document.querySelector(`[data-course-card="${course.id}"]`);
    if (!card) return;

    const doneLessons = course.lessons.filter((l) =>
      (academyProgress.completedLessons || []).includes(l.id)
    ).length;
    const pct = Math.round((doneLessons / course.lessons.length) * 100);

    const readCountEl = card.querySelector('.course-read-count');
    const barEl = card.querySelector('.course-progress-bar');
    const quizScoreBadge = card.querySelector('.course-quiz-status');
    const quizBtn = card.querySelector('.course-quiz-btn');

    if (readCountEl) readCountEl.textContent = `${doneLessons} dari ${course.lessons.length} materi dibaca`;
    if (barEl) barEl.style.width = `${pct}%`;

    const score = academyProgress.completedQuizzes ? academyProgress.completedQuizzes[course.id] : undefined;
    const isPassed = score !== undefined && score >= 70;

    if (quizScoreBadge) {
      if (score !== undefined) {
        quizScoreBadge.className = 'course-quiz-status font-semibold text-emerald-700 font-mono';
        quizScoreBadge.textContent = `Skor Kuis: ${score}%`;
      } else {
        quizScoreBadge.className = 'course-quiz-status text-slate-400';
        quizScoreBadge.textContent = 'Kuis Belum Diikuti';
      }
    }

    if (quizBtn) {
      quizBtn.className = isPassed
        ? 'course-quiz-btn px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100'
        : 'course-quiz-btn px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer bg-cyan-600 text-white hover:bg-cyan-700 shadow-xs';
      const span = quizBtn.querySelector('span');
      if (span) span.textContent = isPassed ? `Lulus (${score}%) · Ulangi` : 'Ikuti Kuis';
    }
  });
}

// ---------------------------------------------------------------------------
// 3. LESSON READER MODAL
// ---------------------------------------------------------------------------
function openLessonReaderModal(courseId) {
  const found = ACADEMY_COURSES.find((c) => c.id === courseId);
  if (!found) return;

  activeReaderCourse = found;
  activeReaderLessonIdx = 0;
  renderLessonReader();

  const modal = document.getElementById('lesson-reader-modal');
  if (modal) modal.classList.remove('hidden');
}

function closeLessonReaderModal() {
  const modal = document.getElementById('lesson-reader-modal');
  if (modal) modal.classList.add('hidden');
  activeReaderCourse = null;
}

function selectReaderLesson(idx) {
  activeReaderLessonIdx = idx;
  renderLessonReader();
}

function prevReaderLesson() {
  if (activeReaderLessonIdx > 0) {
    activeReaderLessonIdx--;
    renderLessonReader();
  }
}

async function persistLessonCompleted(lessonId) {
  if (!academyProgress.completedLessons.includes(lessonId)) {
    academyProgress.completedLessons.push(lessonId);
    syncCourseCardsUI();
    try {
      const res = await fetch(getAppBaseUrl('/api/academy.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'complete_lesson', lessonId }),
      });
      const data = await res.json();
      if (data.progress) {
        academyProgress = data.progress;
        syncCourseCardsUI();
      }
    } catch (e) {
      // ignore offline storage error
    }
  }
}

function nextReaderLesson() {
  if (!activeReaderCourse) return;
  const lesson = activeReaderCourse.lessons[activeReaderLessonIdx];
  if (lesson) persistLessonCompleted(lesson.id);

  if (activeReaderLessonIdx < activeReaderCourse.lessons.length - 1) {
    activeReaderLessonIdx++;
    renderLessonReader();
  } else {
    const course = activeReaderCourse;
    closeLessonReaderModal();
    openQuizPageView(course.id);
  }
}

function switchReaderToQuiz() {
  if (!activeReaderCourse) return;
  const courseId = activeReaderCourse.id;
  closeLessonReaderModal();
  openQuizPageView(courseId);
}

function renderLessonReader() {
  if (!activeReaderCourse) return;
  const lesson = activeReaderCourse.lessons[activeReaderLessonIdx];
  if (!lesson) return;

  document.getElementById('reader-course-category').textContent = activeReaderCourse.category.replace('_', ' ');
  document.getElementById('reader-course-title').textContent = activeReaderCourse.title;
  document.getElementById('reader-lesson-time').textContent = lesson.readingTime;
  document.getElementById('reader-lesson-num').textContent = String(activeReaderLessonIdx + 1);
  document.getElementById('reader-lesson-title').textContent = lesson.title;
  document.getElementById('reader-lesson-summary').textContent = lesson.summary;

  // Sub-navigation tabs
  const tabsEl = document.getElementById('reader-lesson-tabs');
  tabsEl.innerHTML = activeReaderCourse.lessons.map((ls, idx) => {
    const isCompleted = (academyProgress.completedLessons || []).includes(ls.id);
    const isActive = idx === activeReaderLessonIdx;
    const cls = isActive
      ? 'bg-cyan-600 text-white font-semibold shadow-xs'
      : 'bg-white border border-slate-200 text-slate-600 hover:text-slate-900';
    const checkSvg = isCompleted
      ? `<svg class="w-3.5 h-3.5 ${isActive ? 'text-cyan-200' : 'text-emerald-600'}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>`
      : '';
    return `
      <button type="button" onclick="selectReaderLesson(${idx})" class="px-3 py-1.5 rounded-lg whitespace-nowrap shrink-0 flex items-center gap-1.5 transition-colors cursor-pointer ${cls}">
        ${checkSvg}
        <span>${idx + 1}. ${escapeHtml(ls.title.substring(0, 24))}...</span>
      </button>
    `;
  }).join('');

  // Paragraphs
  document.getElementById('reader-lesson-paragraphs').innerHTML = (lesson.contentParagraphs || [])
    .map((p) => `<p class="text-slate-700 leading-relaxed text-sm">${escapeHtml(p)}</p>`)
    .join('');

  // Tips Box
  const tipsBox = document.getElementById('reader-tips-box');
  const tipsList = document.getElementById('reader-tips-list');
  if (lesson.practicalTips && lesson.practicalTips.length > 0) {
    tipsList.innerHTML = lesson.practicalTips
      .map((t) => `<li class="leading-relaxed">${escapeHtml(t)}</li>`)
      .join('');
    tipsBox.classList.remove('hidden');
  } else {
    tipsBox.classList.add('hidden');
  }

  // Case Study Box
  const csBox = document.getElementById('reader-casestudy-box');
  if (lesson.caseStudy) {
    document.getElementById('reader-casestudy-title').textContent = lesson.caseStudy.title;
    document.getElementById('reader-casestudy-scenario').textContent = lesson.caseStudy.scenario;
    document.getElementById('reader-casestudy-learned').textContent = lesson.caseStudy.lessonLearned;
    csBox.classList.remove('hidden');
  } else {
    csBox.classList.add('hidden');
  }

  // Footer Controls
  document.getElementById('reader-prev-btn').disabled = activeReaderLessonIdx === 0;
  document.getElementById('reader-open-quiz-label').textContent = `Buka Kuis (${activeReaderCourse.quiz.length} Soal)`;
  document.getElementById('reader-next-label').textContent =
    activeReaderLessonIdx < activeReaderCourse.lessons.length - 1
      ? 'Selesai & Lanjut Materi'
      : 'Selesaikan & Uji Pemahaman';
}

// ---------------------------------------------------------------------------
// 4. INTERACTIVE 10-QUESTION QUIZ PAGE VIEW
// ---------------------------------------------------------------------------
function openQuizPageView(courseId) {
  const found = ACADEMY_COURSES.find((c) => c.id === courseId);
  if (!found) return;

  activeQuizCourse = found;
  restartQuiz(false);

  document.getElementById('academy-main-view').classList.add('hidden');
  document.getElementById('quiz-page-wrapper').classList.remove('hidden');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function closeQuizPageView() {
  document.getElementById('quiz-page-wrapper').classList.add('hidden');
  document.getElementById('academy-main-view').classList.remove('hidden');
  activeQuizCourse = null;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function restartQuiz(shouldScroll = true) {
  quizIndex = 0;
  quizSelectedAnswers = {};
  isQuizConfirmed = false;
  quizStreak = 0;
  quizMaxStreak = 0;
  quizPointsEarned = 0;

  document.getElementById('quiz-question-screen').classList.remove('hidden');
  document.getElementById('quiz-result-screen').classList.add('hidden');
  document.getElementById('quiz-progress-track').classList.remove('hidden');
  document.getElementById('quiz-sim-result-box').classList.add('hidden');
  document.getElementById('quiz-streak-bonus-banner').classList.add('hidden');

  renderQuizQuestion();
  if (shouldScroll) window.scrollTo({ top: 0, behavior: 'smooth' });
}

function selectQuizOption(optIdx) {
  if (isQuizConfirmed) return;
  quizSelectedAnswers[quizIndex] = optIdx;
  renderQuizQuestion();
}

function renderQuizQuestion() {
  if (!activeQuizCourse) return;
  const questions = activeQuizCourse.quiz.slice(0, 10);
  const currentQ = questions[quizIndex];
  const selectedOpt = quizSelectedAnswers[quizIndex];
  const hasSelected = selectedOpt !== undefined;

  document.getElementById('quiz-course-title').textContent = activeQuizCourse.title;
  document.getElementById('quiz-live-points').textContent = String(quizPointsEarned);
  document.getElementById('quiz-q-number').textContent = String(quizIndex + 1);
  document.getElementById('quiz-q-total').textContent = String(questions.length);
  document.getElementById('quiz-q-prompt').textContent = currentQ.question;

  // Progress Bar
  const pct = Math.round(((quizIndex + 1) / questions.length) * 100);
  document.getElementById('quiz-progress-bar').style.width = `${pct}%`;

  // Streak badge
  const streakBadge = document.getElementById('quiz-live-streak-badge');
  if (quizStreak >= 2) {
    document.getElementById('quiz-live-streak-count').textContent = String(quizStreak);
    streakBadge.classList.remove('hidden');
  } else {
    streakBadge.classList.add('hidden');
  }

  // Options A/B/C/D
  const letters = ['A', 'B', 'C', 'D'];
  const optionsEl = document.getElementById('quiz-options-list');
  optionsEl.innerHTML = currentQ.options.map((opt, idx) => {
    const isSelected = selectedOpt === idx;
    const isCorrect = currentQ.correctAnswer === idx;

    let borderCls = 'border-slate-200 bg-white hover:border-cyan-300 hover:bg-slate-50/80 cursor-pointer';
    let badgeCls = 'bg-slate-100 text-slate-700';
    let stateIcon = '';

    if (isQuizConfirmed) {
      if (isCorrect) {
        borderCls = 'border-emerald-500 bg-emerald-50/90 text-emerald-950 ring-2 ring-emerald-400 shadow-xs';
        badgeCls = 'bg-emerald-600 text-white';
        stateIcon = `<svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>`;
      } else if (isSelected && !isCorrect) {
        borderCls = 'border-rose-400 bg-rose-50 text-rose-950 ring-2 ring-rose-300';
        badgeCls = 'bg-rose-500 text-white';
        stateIcon = `<svg class="w-5 h-5 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>`;
      } else {
        borderCls = 'border-slate-100 bg-slate-50 text-slate-400 opacity-60';
      }
    } else if (isSelected) {
      borderCls = 'border-cyan-600 bg-cyan-50/70 text-cyan-950 ring-2 ring-cyan-500 shadow-xs cursor-pointer';
      badgeCls = 'bg-cyan-600 text-white';
    }

    return `
      <button
        type="button"
        ${isQuizConfirmed ? 'disabled' : ''}
        onclick="selectQuizOption(${idx})"
        class="w-full text-left p-4 sm:p-5 rounded-2xl border transition-all flex items-start gap-3.5 relative overflow-hidden ${borderCls}"
      >
        <span class="w-8 h-8 rounded-xl shrink-0 font-bold text-xs flex items-center justify-center transition-colors ${badgeCls}">
          ${letters[idx]}
        </span>
        <div class="flex-1 pt-1">
          <span class="text-xs sm:text-sm font-medium leading-relaxed block">${escapeHtml(opt)}</span>
        </div>
        ${stateIcon ? `<div class="shrink-0 pt-1">${stateIcon}</div>` : ''}
      </button>
    `;
  }).join('');

  // Action Area
  const confirmWrap = document.getElementById('quiz-confirm-wrap');
  const confirmBtn = document.getElementById('quiz-confirm-btn');
  const confirmLabel = document.getElementById('quiz-confirm-label');
  const explWrap = document.getElementById('quiz-explanation-wrap');
  const explBox = document.getElementById('quiz-explanation-box');
  const explHead = document.getElementById('quiz-explanation-heading');
  const explText = document.getElementById('quiz-explanation-text');
  const nextLabel = document.getElementById('quiz-next-label');

  if (isQuizConfirmed) {
    confirmWrap.classList.add('hidden');
    explWrap.classList.remove('hidden');

    const isAnswerCorrect = selectedOpt === currentQ.correctAnswer;
    explBox.className = isAnswerCorrect
      ? 'p-5 rounded-2xl border bg-emerald-50/80 border-emerald-200 text-emerald-950'
      : 'p-5 rounded-2xl border bg-amber-50/80 border-amber-200 text-amber-950';

    explHead.innerHTML = isAnswerCorrect
      ? `<svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg><span>Jawaban Tepat! (+10 Poin &amp; Diskon Tagihan Bertambah)</span>`
      : `<svg class="w-4 h-4 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg><span>Penjelasan Pemahaman:</span>`;

    explText.textContent = currentQ.explanation;
    nextLabel.textContent =
      quizIndex < questions.length - 1
        ? `Lanjut ke Soal ${quizIndex + 2} dari ${questions.length}`
        : 'Selesaikan & Lihat Total Poin Diskon';
  } else {
    explWrap.classList.add('hidden');
    confirmWrap.classList.remove('hidden');
    confirmBtn.disabled = !hasSelected;
    confirmBtn.className = hasSelected
      ? 'w-full py-4 rounded-2xl font-extrabold text-sm shadow-sm transition-all flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-500 text-white cursor-pointer shadow-cyan-600/20'
      : 'w-full py-4 rounded-2xl font-extrabold text-sm shadow-sm transition-all flex items-center justify-center gap-2 bg-slate-200 text-slate-400 cursor-not-allowed';
    confirmLabel.textContent = hasSelected ? 'Kunci & Periksa Jawaban' : 'Pilih Salah Satu Jawaban di Atas';
  }
}

function confirmQuizAnswer() {
  if (!activeQuizCourse || isQuizConfirmed) return;
  const questions = activeQuizCourse.quiz.slice(0, 10);
  const currentQ = questions[quizIndex];
  const selectedOpt = quizSelectedAnswers[quizIndex];
  if (selectedOpt === undefined) return;

  isQuizConfirmed = true;
  const isCorrect = selectedOpt === currentQ.correctAnswer;

  if (isCorrect) {
    quizStreak++;
    if (quizStreak > quizMaxStreak) quizMaxStreak = quizStreak;
    const bonus = quizStreak >= 3 ? 5 : 0;
    quizPointsEarned += 10 + bonus;

    if (quizStreak >= 3) {
      const banner = document.getElementById('quiz-streak-bonus-banner');
      document.getElementById('quiz-streak-banner-num').textContent = String(quizStreak);
      banner.classList.remove('hidden');
      setTimeout(() => banner.classList.add('hidden'), 2000);
    }
  } else {
    quizStreak = 0;
  }

  renderQuizQuestion();
}

async function nextQuizQuestion() {
  if (!activeQuizCourse) return;
  const questions = activeQuizCourse.quiz.slice(0, 10);

  if (quizIndex < questions.length - 1) {
    quizIndex++;
    isQuizConfirmed = false;
    document.getElementById('quiz-streak-bonus-banner').classList.add('hidden');
    renderQuizQuestion();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  // Final Result Screen
  let correctCount = 0;
  questions.forEach((q, idx) => {
    if (quizSelectedAnswers[idx] === q.correctAnswer) correctCount++;
  });
  const score = Math.round((correctCount / questions.length) * 100);
  const isPassed = score >= 70;
  const earnedTotal = quizPointsEarned || correctCount * 10;
  const discountRp = earnedTotal * 100;
  const voucherCode = `DISC-TIRTA-${String(window.SWC_METER_SUFFIX || '2026').slice(-4)}-${score}`;

  document.getElementById('quiz-question-screen').classList.add('hidden');
  document.getElementById('quiz-progress-track').classList.add('hidden');
  document.getElementById('quiz-result-screen').classList.remove('hidden');

  document.getElementById('quiz-result-title').textContent = isPassed
    ? 'Luar Biasa, Sanak Warga!'
    : 'Terus Semangat Belajar!';
  document.getElementById('quiz-result-subtitle').textContent = isPassed
    ? `Anda berhasil menjawab ${correctCount} dari 10 soal dengan benar. Pemahaman air Anda sangat menginspirasi!`
    : `Anda menjawab ${correctCount} dari 10 soal benar. Anda bisa mengulang kuis kapan pun untuk meningkatkan poin diskon.`;

  document.getElementById('quiz-res-score').textContent = `${score}%`;
  document.getElementById('quiz-res-correct').textContent = `${correctCount}/10`;
  document.getElementById('quiz-res-streak').textContent = `${quizMaxStreak}x`;
  document.getElementById('quiz-res-coins').textContent = `+${earnedTotal}`;
  document.getElementById('quiz-res-discount').textContent = `Rp ${discountRp.toLocaleString('id-ID')},-`;
  document.getElementById('quiz-voucher-code').textContent = voucherCode;
  document.getElementById('quiz-copy-btn-label').textContent = 'Salin Kode';

  // Review Breakdown
  document.getElementById('quiz-review-list').innerHTML = questions
    .map((q, qIdx) => {
      const userAns = quizSelectedAnswers[qIdx];
      const ok = userAns === q.correctAnswer;
      return `
        <div class="p-4 rounded-2xl border text-xs space-y-2 ${ok ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-rose-200'}">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2.5 font-bold text-slate-900">
              <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[11px] flex items-center justify-center shrink-0 mt-0.5">${qIdx + 1}</span>
              <span>${escapeHtml(q.question)}</span>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 ${ok ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
              ${ok ? 'Benar' : 'Salah'}
            </span>
          </div>
          <div class="pl-7 space-y-1 text-slate-600">
            <div><strong>Jawaban Anda:</strong> ${escapeHtml(userAns !== undefined ? q.options[userAns] : 'Tidak dijawab')}</div>
            ${!ok ? `<div class="text-emerald-800 font-semibold"><strong>Kunci Jawaban:</strong> ${escapeHtml(q.options[q.correctAnswer])}</div>` : ''}
            <p class="text-[11px] text-slate-500 pt-1 border-t border-slate-100/80">${escapeHtml(q.explanation)}</p>
          </div>
        </div>
      `;
    })
    .join('');

  // Persist to PHP Session
  const finalXpAward = correctCount * 10 + (score >= 80 ? 20 : 0);
  try {
    const res = await fetch(getAppBaseUrl('/api/academy.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'complete_quiz',
        courseId: activeQuizCourse.id,
        score,
        xpReward: finalXpAward,
      }),
    });
    const data = await res.json();
    if (data.progress) {
      academyProgress = data.progress;
      syncCourseCardsUI();
    }
  } catch (e) {
    // ignore
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function copyQuizVoucher() {
  const code = document.getElementById('quiz-voucher-code')?.textContent.trim();
  const label = document.getElementById('quiz-copy-btn-label');
  if (code && navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(code);
  }
  if (label) {
    label.textContent = '✓ Tersalin!';
    setTimeout(() => (label.textContent = 'Salin Kode'), 2500);
  }
}

function simulateMobileBillDiscount() {
  const discountText = document.getElementById('quiz-res-discount')?.textContent || 'Rp 0,-';
  const rawCoins = parseInt((document.getElementById('quiz-res-coins')?.textContent || '0').replace(/\D/g, ''), 10) || 0;
  const discountRp = rawCoins * 100;
  const remaining = Math.max(0, 75000 - discountRp);

  const box = document.getElementById('quiz-sim-result-box');
  const textEl = document.getElementById('quiz-sim-result-text');
  if (textEl) {
    textEl.innerHTML = `Tagihan normal rekening air Anda misal sebesar <strong>Rp 75.000,-</strong> otomatis terpotong <strong>${escapeHtml(discountText)}</strong> berkat koin kuis ini. Anda cukup membayar <strong>Rp ${remaining.toLocaleString('id-ID')},-</strong> saat checkout di aplikasi mobile!`;
  }
  if (box) box.classList.remove('hidden');
}

// ---------------------------------------------------------------------------
// 5. WATER CALCULATOR
// ---------------------------------------------------------------------------
let calcMembers = 4;
let calcBath = 'gayung';
let calcTeeth = 'kran_mengalir';
let calcDish = 'kran_bebas';

function setCalcFamilyMembers(val) {
  calcMembers = Number(val) || 4;
  document.getElementById('calc-members-label').textContent = String(calcMembers);
  recalcWaterUsage();
}

function setCalcBathMethod(val) {
  calcBath = val;
  document.querySelectorAll('.calc-bath-btn').forEach((btn) => {
    const active = btn.getAttribute('data-calc-bath') === val;
    btn.className = active
      ? 'calc-bath-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-cyan-600 bg-cyan-50/60 ring-1 ring-cyan-600'
      : 'calc-bath-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white';
  });
  recalcWaterUsage();
}

function setCalcTeethHabit(val) {
  calcTeeth = val;
  document.querySelectorAll('.calc-teeth-btn').forEach((btn) => {
    const active = btn.getAttribute('data-calc-teeth') === val;
    btn.className = active
      ? 'calc-teeth-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-cyan-600 bg-cyan-50/60 ring-1 ring-cyan-600'
      : 'calc-teeth-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white';
  });
  recalcWaterUsage();
}

function setCalcDishMethod(val) {
  calcDish = val;
  document.querySelectorAll('.calc-dish-btn').forEach((btn) => {
    const active = btn.getAttribute('data-calc-dish') === val;
    btn.className = active
      ? 'calc-dish-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-cyan-600 bg-cyan-50/60 ring-1 ring-cyan-600'
      : 'calc-dish-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white';
  });
  recalcWaterUsage();
}

function resetWaterCalculator() {
  document.getElementById('calc-members-range').value = '4';
  setCalcFamilyMembers(4);
  setCalcBathMethod('gayung');
  setCalcTeethHabit('kran_mengalir');
  setCalcDishMethod('kran_bebas');
}

function calculatePayakumbuhBill(m3) {
  const fixedFee = 15000;
  let waterCost = 0;
  if (m3 <= 10) waterCost = m3 * 2800;
  else if (m3 <= 20) waterCost = 10 * 2800 + (m3 - 10) * 4200;
  else waterCost = 10 * 2800 + 10 * 4200 + (m3 - 20) * 6500;
  return Math.round(fixedFee + waterCost);
}

function recalcWaterUsage() {
  let bathPerPerson = 75 * 2;
  if (calcBath === 'shower_biasa') bathPerPerson = 6 * 10 * 2;
  else if (calcBath === 'shower_hemat') bathPerPerson = 6 * 5.5 * 2;

  const totalBath = bathPerPerson * calcMembers;
  const totalTeeth = (calcTeeth === 'kran_mengalir' ? 12 : 1) * calcMembers;
  const dishDaily = calcDish === 'kran_bebas' ? 90 : 35;
  const laundryDaily = (4 * 110) / 7;
  const gardenDaily = (3 * 40) / 7;
  const cookingDaily = calcMembers * 8;

  const totalDaily = Math.round(totalBath + totalTeeth + dishDaily + laundryDaily + gardenDaily + cookingDaily);
  const monthlyLiters = totalDaily * 30;
  const monthlyM3 = +(monthlyLiters / 1000).toFixed(1);
  const estBill = calculatePayakumbuhBill(monthlyM3);

  const optBath = calcMembers * (5 * 5.5 * 2);
  const optTeeth = calcMembers * 1;
  const optDaily = Math.round(optBath + optTeeth + 35 + laundryDaily + gardenDaily + cookingDaily);
  const optMonthlyM3 = +((optDaily * 30) / 1000).toFixed(1);
  const optBill = calculatePayakumbuhBill(optMonthlyM3);

  const savedLiters = Math.max(0, monthlyLiters - optDaily * 30);
  const savedIdr = Math.max(0, estBill - optBill);

  document.getElementById('calc-out-daily').textContent = String(totalDaily);
  document.getElementById('calc-out-per-person').textContent = `${Math.round(totalDaily / calcMembers)} L`;
  document.getElementById('calc-out-m3').textContent = `${monthlyM3} m³`;
  document.getElementById('calc-out-bill').textContent = `Rp ${estBill.toLocaleString('id-ID')}`;
  document.getElementById('calc-out-saved-liters').textContent = `+${Math.round(savedLiters).toLocaleString('id-ID')} Liter`;
  document.getElementById('calc-out-saved-idr').textContent = `Rp ${savedIdr.toLocaleString('id-ID')} / bulan`;
}

// ---------------------------------------------------------------------------
// 6. TIRTABOT AI CHATBOT WIDGET
// ---------------------------------------------------------------------------
const CHAT_STORAGE_KEY = 'smartwater_tirtabot_history_php';
let chatMessages = [];
let isChatOpen = false;
let isChatMinimized = false;
let isChatLoading = false;

function getDefaultGreeting() {
  return {
    id: 'msg-welcome',
    role: 'model',
    content: 'Halo! Saya **TirtaBot**, asisten cerdas Komunitas Smart Water Kota Payakumbuh.\n\nAda yang bisa saya bantu seputar materi Akademi Air, cara baca meteran, tarif air Tirta Sago, atau tips hemat air?',
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
  };
}

function loadChatHistory() {
  try {
    const saved = localStorage.getItem(CHAT_STORAGE_KEY);
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) {
        chatMessages = parsed;
        return;
      }
    }
  } catch (e) {}
  chatMessages = [getDefaultGreeting()];
}

function saveChatHistory() {
  try {
    localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(chatMessages));
  } catch (e) {}
}

function formatBotMarkdown(text) {
  let safe = escapeHtml(text);
  safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold">$1</strong>');
  safe = safe.replace(/\*(.*?)\*/g, '<em class="italic">$1</em>');
  safe = safe.replace(/`([^`]+)`/g, '<code class="px-1 py-0.5 rounded bg-slate-100 text-cyan-800 font-mono text-[10px]">$1</code>');
  return safe.replace(/\n/g, '<br>');
}

function renderChatMessages() {
  const container = document.getElementById('tirtabot-messages');
  if (!container) return;
  container.querySelectorAll('.tirtabot-msg-row, #tirtabot-loading-row').forEach((n) => n.remove());

  chatMessages.forEach((msg) => {
    const isUser = msg.role === 'user';
    const row = document.createElement('div');
    row.className = `tirtabot-msg-row flex flex-col max-w-[85%] ${isUser ? 'ml-auto items-end' : 'mr-auto items-start'}`;
    const bubbleClass = isUser
      ? 'bg-gradient-to-br from-cyan-600 to-sky-600 text-white rounded-tr-sm'
      : 'bg-white text-slate-700 border border-slate-200/80 rounded-tl-sm';
    const contentHtml = isUser ? escapeHtml(msg.content).replace(/\n/g, '<br>') : formatBotMarkdown(msg.content);

    row.innerHTML = `
      <div class="px-3.5 py-2.5 rounded-2xl text-xs leading-relaxed shadow-xs ${bubbleClass}">
        <div class="space-y-1 break-words">${contentHtml}</div>
      </div>
      <span class="text-[9px] text-slate-400 mt-1 px-1">${escapeHtml(msg.timestamp || '')}</span>
    `;
    container.appendChild(row);
  });

  if (isChatLoading) {
    const loadingRow = document.createElement('div');
    loadingRow.id = 'tirtabot-loading-row';
    loadingRow.className = 'flex items-center gap-2 max-w-[75%] mr-auto';
    loadingRow.innerHTML = `
      <div class="px-3.5 py-2.5 rounded-2xl rounded-tl-sm bg-white border border-slate-200 shadow-xs flex items-center gap-2 text-xs text-slate-500">
        <span class="flex gap-1">
          <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:0ms"></span>
          <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:150ms"></span>
          <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:300ms"></span>
        </span>
        <span class="text-[11px] font-medium">TirtaBot sedang mengetik...</span>
      </div>
    `;
    container.appendChild(loadingRow);
  }
  container.scrollTop = container.scrollHeight;
}

function openTirtaBot() {
  isChatOpen = true;
  isChatMinimized = false;
  document.getElementById('tirtabot-trigger-wrap')?.classList.add('hidden');
  document.getElementById('tirtabot-window')?.classList.remove('hidden');
  updateMinimizedState();
  renderChatMessages();
  setTimeout(() => document.getElementById('tirtabot-input')?.focus(), 150);
}

function closeTirtaBot() {
  isChatOpen = false;
  document.getElementById('tirtabot-window')?.classList.add('hidden');
  document.getElementById('tirtabot-trigger-wrap')?.classList.remove('hidden');
}

function toggleMinimizeTirtaBot() {
  isChatMinimized = !isChatMinimized;
  updateMinimizedState();
}

function updateMinimizedState() {
  const win = document.getElementById('tirtabot-window');
  const minBar = document.getElementById('tirtabot-minimized-bar');
  const bodyWrap = document.getElementById('tirtabot-body-wrap');
  const iconMin = document.getElementById('tirtabot-icon-minimize');
  const iconMax = document.getElementById('tirtabot-icon-maximize');
  if (!win) return;

  if (isChatMinimized) {
    win.classList.remove('h-[580px]', 'max-h-[85vh]');
    win.classList.add('h-14');
    minBar?.classList.remove('hidden');
    bodyWrap?.classList.add('hidden');
    iconMin?.classList.add('hidden');
    iconMax?.classList.remove('hidden');
  } else {
    win.classList.remove('h-14');
    win.classList.add('h-[580px]', 'max-h-[85vh]');
    minBar?.classList.add('hidden');
    bodyWrap?.classList.remove('hidden');
    iconMin?.classList.remove('hidden');
    iconMax?.classList.add('hidden');
  }
}

function clearTirtaBotHistory() {
  chatMessages = [getDefaultGreeting()];
  saveChatHistory();
  renderChatMessages();
}

function handleTirtaBotSubmit(event) {
  event.preventDefault();
  const input = document.getElementById('tirtabot-input');
  if (!input) return;
  const text = input.value.trim();
  if (text) sendTirtaBotMessage(text);
}

async function sendTirtaBotMessage(text) {
  if (!text || isChatLoading) return;
  const input = document.getElementById('tirtabot-input');
  if (input) input.value = '';

  const historySnapshot = chatMessages.map((m) => ({ role: m.role, content: m.content }));
  chatMessages.push({
    id: 'msg-user-' + Date.now(),
    role: 'user',
    content: text,
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
  });
  saveChatHistory();
  isChatLoading = true;
  renderChatMessages();

  try {
    const res = await fetch(getAppBaseUrl('/api/chat.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text, history: historySnapshot }),
    });
    const data = await res.json();
    chatMessages.push({
      id: 'msg-bot-' + Date.now(),
      role: 'model',
      content: data.reply || 'Maaf, terjadi gangguan saat memproses pertanyaan Anda.',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    });
  } catch (e) {
    chatMessages.push({
      id: 'msg-err-' + Date.now(),
      role: 'model',
      content: '⚠️ Koneksi ke server PHP gagal. Jalankan folder ini melalui server PHP lokal Anda.',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    });
  } finally {
    isChatLoading = false;
    saveChatHistory();
    renderChatMessages();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  syncCourseCardsUI();
  recalcWaterUsage();
  loadChatHistory();
  renderChatMessages();
});
