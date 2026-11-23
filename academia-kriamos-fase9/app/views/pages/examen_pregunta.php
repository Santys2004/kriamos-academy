<section class="py-5 exam-screen">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card card-kriamos overflow-hidden">
                    <div class="exam-header p-4 text-white">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <span class="badge bg-kriamos-orange mb-2"><?= e($attempt['course_code']) ?></span>
                                <h1 class="h4 fw-bold mb-1"><?= e($attempt['module_title']) ?></h1>
                                <p class="mb-0 opacity-90"><?= e($attempt['full_name']) ?> · Intento <?= (int)$attempt['attempt_number'] ?></p>
                            </div>
                            <div class="text-md-end">
                                <div class="timer-box" id="timer" data-seconds="<?= (int)$secondsRemaining ?>"><?= (int)$secondsRemaining ?></div>
                                <div class="small opacity-90">segundos restantes</div>
                            </div>
                        </div>
                    </div>

                    <div class="progress rounded-0" style="height: 8px;">
                        <?php $percentage = (($progress['answered'] + 1) / max(1, $progress['total'])) * 100; ?>
                        <div class="progress-bar bg-kriamos-orange" style="width: <?= e((string)$percentage) ?>%"></div>
                    </div>

                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="badge text-bg-light border">Pregunta <?= (int)$question['question_order'] ?> de <?= (int)$progress['total'] ?></span>
                            <span class="badge <?= $question['difficulty'] === 'critica' ? 'text-bg-danger' : ($question['difficulty'] === 'media' ? 'text-bg-warning' : 'text-bg-success') ?>">
                                <?= e(ucfirst($question['difficulty'])) ?>
                            </span>
                        </div>

                        <h2 class="h4 fw-bold text-kriamos-blue mb-4"><?= e($question['question_text']) ?></h2>

                        <form id="examQuestionForm" action="<?= e(current_url('guardar_respuesta')) ?>" method="post">
                            <input type="hidden" name="attempt_id" value="<?= (int)$attempt['id'] ?>">
                            <input type="hidden" name="attempt_question_id" value="<?= (int)$question['id'] ?>">

                            <div class="vstack gap-3 mb-4">
                                <?php foreach ($options as $option): ?>
                                    <label class="answer-option d-flex align-items-start gap-3 p-3 border rounded-4">
                                        <input type="radio" name="selected_option_id" value="<?= (int)$option['id'] ?>" class="form-check-input mt-1" required>
                                        <span>
                                            <strong class="me-2"><?= e($option['option_label']) ?>.</strong><?= e($option['option_text']) ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="alert alert-light border small">
                                <i class="fa-solid fa-lock me-2 text-kriamos-orange"></i>Al enviar, la respuesta quedará bloqueada y no podrá modificarse.
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-kriamos-orange btn-lg" id="submitAnswerBtn">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Enviar respuesta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const timer = document.getElementById('timer');
    const form = document.getElementById('examQuestionForm');
    const submitBtn = document.getElementById('submitAnswerBtn');
    if (!timer || !form) return;

    let seconds = parseInt(timer.dataset.seconds || '60', 10);
    let submitted = false;

    const tick = () => {
        seconds = Math.max(0, seconds);
        timer.textContent = seconds.toString();
        if (seconds <= 10) {
            timer.classList.add('timer-danger');
        }
        if (seconds <= 0 && !submitted) {
            submitted = true;
            if (submitBtn) submitBtn.disabled = true;
            form.submit();
            return;
        }
        seconds--;
        setTimeout(tick, 1000);
    };

    form.addEventListener('submit', function () {
        submitted = true;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Enviando...';
        }
    });

    tick();
});
</script>
