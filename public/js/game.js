(() => {
    'use strict';

    const root = document.getElementById('game');
    if (!root) return;

    const { startUrl, answerUrl, valueLabel } = root.dataset;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const template = document.getElementById('card-template');
    const slots = {
        left: document.getElementById('slot-left'),
        right: document.getElementById('slot-right'),
    };
    const scoreEl = document.getElementById('score');
    const hintEl = document.getElementById('hint');
    const pickerEl = document.getElementById('picker');
    const arenaEl = document.getElementById('arena');
    const levelBar = document.getElementById('level-bar');
    const levelChip = document.getElementById('level-chip');
    const overEl = document.getElementById('over');
    const finalScoreEl = document.getElementById('final-score');
    const overTitle = document.getElementById('over-title');
    const noteCompleted = document.getElementById('note-completed');
    const noteSaved = document.getElementById('note-saved');
    const noteGuest = document.getElementById('note-guest');
    const restartBtn = document.getElementById('restart');

    const HINT_READY = 'Слева значение известно, справа скрыто. Нажми на машину, у которой оно больше.';

    let busy = false; // пока идёт запрос или показ ответа, клики игнорируются
    let level = null; // выбранный уровень: { key, label }

    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    async function api(url, body = {}) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body),
        });

        if (!response.ok) {
            const error = new Error('HTTP ' + response.status);
            error.status = response.status;
            throw error;
        }

        return response.json();
    }

    const cardOf = (side) => slots[side].firstElementChild;
    const part = (card, name) => card.querySelector('[data-slot="' + name + '"]');

    function setValue(card, display) {
        const value = part(card, 'value');
        // textContent вместо innerHTML: данные с сервера не попадут в разметку как HTML
        value.textContent = display ?? '???';
        value.classList.toggle('text-ink-muted', display == null);
    }

    function buildCard(car, side) {
        const card = template.content.firstElementChild.cloneNode(true);
        const image = part(card, 'image');
        image.src = car.image;
        image.alt = car.title;

        part(card, 'meta').textContent = car.brand + ' · ' + car.year;
        part(card, 'model').textContent = car.model;
        part(card, 'credit').textContent = car.credit ?? '';
        part(card, 'label').textContent = valueLabel;
        setValue(card, car.display);

        card.addEventListener('click', () => choose(side));
        return card;
    }

    function renderCard(side, car, animate = false) {
        const card = buildCard(car, side);
        if (animate) card.classList.add('card-enter');
        slots[side].replaceChildren(card);
    }

    function setCardsDisabled(disabled) {
        ['left', 'right'].forEach((side) => {
            const card = cardOf(side);
            if (card) card.disabled = disabled;
        });
    }

    function setHint(text) {
        hintEl.textContent = text;
    }

    // Экран выбора уровня
    function showPicker() {
        busy = false;
        pickerEl.hidden = false;
        arenaEl.hidden = true;
        overEl.hidden = true;
        levelBar.hidden = true;
        scoreEl.textContent = '0';
        setHint('Выбери уровень сложности.');
    }

    // Победившая карточка зелёная, неверный выбор красный
    function reveal(chosen, data) {
        ['left', 'right'].forEach((side) => {
            const card = cardOf(side);
            setValue(card, data[side].display);

            const verdict = part(card, 'verdict');
            if (side === data.winner) {
                card.dataset.state = 'correct';
                verdict.textContent = side === chosen ? 'Верно' : 'Больше';
            } else if (side === chosen) {
                card.dataset.state = 'wrong';
                verdict.textContent = 'Неверно';
            } else {
                card.dataset.state = 'dim';
            }
        });
    }

    function showOver(data) {
        const completed = Boolean(data.completed);
        overTitle.textContent = completed ? 'Все машины пройдены' : 'Партия окончена';
        noteCompleted.hidden = !completed;
        finalScoreEl.textContent = data.score;
        noteSaved.hidden = !data.saved;
        noteGuest.hidden = data.saved;
        overEl.hidden = false;
        setHint('Игра окончена.');
        restartBtn.focus();
    }

    function handleError(error) {
        if (error.status === 419) { location.reload(); return; } // токен или сессия устарели
        if (error.status === 409) { start(); return; } // партия потерялась, начинаем заново

        if (error.status === 429) {
            setHint('Слишком быстро. Подожди секунду и нажми снова.');
            setCardsDisabled(false);
        } else {
            setHint('Не удалось связаться с сервером. Обнови страницу.');
        }
        busy = false;
    }

    async function choose(side) {
        if (busy) return;
        busy = true;
        setCardsDisabled(true);

        try {
            const data = await api(answerUrl, { choice: side });
            reveal(side, data);
            setHint(data.completed ? 'Верно! Подходящие машины закончились.' : (data.correct ? 'Верно! +1 к счёту.' : 'Неверно.'));
            scoreEl.textContent = data.score;

            await sleep(data.correct ? 1500 : 1800);

            if (data.game_over) {
                showOver(data);
                return;
            }

            // Победитель остаётся слева, справа появляется новая машина
            renderCard('left', data.next.left);
            renderCard('right', data.next.right, true);
            setHint(HINT_READY);
            busy = false;
        } catch (error) {
            handleError(error);
        }
    }

    async function start(chosen = level) {
        if (!chosen) { showPicker(); return; }

        level = chosen;
        busy = true;
        pickerEl.hidden = true;
        arenaEl.hidden = false;
        overEl.hidden = true;
        levelBar.hidden = false;
        levelChip.textContent = 'Уровень: ' + level.label;
        scoreEl.textContent = '0';
        slots.left.replaceChildren();
        slots.right.replaceChildren();
        setHint('Загружаем машины...');

        try {
            const data = await api(startUrl, { level: level.key });
            renderCard('left', data.left);
            renderCard('right', data.right);
            setHint(HINT_READY);
            busy = false;
        } catch (error) {
            handleError(error);
        }
    }

    pickerEl.querySelectorAll('[data-level]').forEach((button) => {
        button.addEventListener('click', () => {
            start({ key: button.dataset.level, label: button.dataset.label });
        });
    });
    document.querySelectorAll('.js-change-level').forEach((button) => {
        button.addEventListener('click', showPicker);
    });
    restartBtn.addEventListener('click', () => start());

    showPicker();
})();
