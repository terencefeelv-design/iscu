// assets/subscribe.js
const questions = [
  {
    question: "Wer bist du?",
    options: [
      "Inhaber oder Mitinhaber des Unternehmens",
      "Geschäftsführer",
      "HR",
      "Projektleiter oder Sonstiges"
    ]
  },
  {
    question: "Was suchst du?",
    options: [
      "Neue Mitarbeitende finden",
      "Employer Branding aufbauen",
      "Beratung zur Digitalisierung"
    ]
  },
  {
    question: "Erzähl uns mehr über deine Wünsche",
    textarea: true
  },
  {
    question: "Kontaktinformationen",
    form: [
      { placeholder: "Vorname",      type: "text",  key: "firstname" },
      { placeholder: "Nachname",     type: "text",  key: "lastname"  },
      { placeholder: "E-Mail",       type: "email", key: "email"     },
      { placeholder: "Telefonnummer",type: "text",  key: "phone"     }
    ]
  }
];

// Collected answers
const answers = { role: '', goal: '', message: '', firstname: '', lastname: '', email: '', phone: '' };

let step = 0;
const container = document.getElementById("form-container");
const progress  = document.getElementById("progress-step");
const nextBtn   = document.getElementById("next");
const prevBtn   = document.getElementById("prev");

function renderStep() {
  const data = questions[step];
  container.innerHTML = '';
  progress.textContent = `Schritt ${step + 1} von ${questions.length}`;

  const h2 = document.createElement("h2");
  h2.textContent = data.question;
  container.appendChild(h2);

  if (data.options) {
    data.options.forEach(opt => {
      const btn = document.createElement("button");
      btn.textContent = opt;
      btn.className = "answer-btn";
      // Highlight previously selected
      if ((step === 0 && answers.role === opt) || (step === 1 && answers.goal === opt)) {
        btn.style.background = '#1d1d1f';
        btn.style.color = '#f5f5f7';
      }
      btn.onclick = () => {
        if (step === 0) answers.role = opt;
        if (step === 1) answers.goal = opt;
        nextStep();
      };
      container.appendChild(btn);
    });
  }

  if (data.textarea) {
    const ta = document.createElement("textarea");
    ta.placeholder = "Ich möchte neue Fachkräfte gewinnen...";
    ta.rows = 4;
    ta.value = answers.message;
    ta.oninput = () => { answers.message = ta.value; };
    container.appendChild(ta);
  }

  if (data.form) {
    data.form.forEach(f => {
      const input = document.createElement("input");
      input.type        = f.type;
      input.placeholder = f.placeholder;
      input.value       = answers[f.key] || '';
      input.oninput     = () => { answers[f.key] = input.value; };
      container.appendChild(input);
    });
  }

  prevBtn.disabled = step === 0;

  // Last step → show "Absenden" instead of "Weiter"
  if (step === questions.length - 1) {
    nextBtn.textContent = 'Absenden';
    nextBtn.style.display = 'inline-block';
  } else {
    nextBtn.textContent = 'Weiter';
    nextBtn.style.display = data.options ? 'none' : 'inline-block';
  }
}

function nextStep() {
  if (step < questions.length - 1) {
    step++;
    renderStep();
  } else {
    submitForm();
  }
}

async function submitForm() {
  nextBtn.disabled = true;
  nextBtn.textContent = '…';

  try {
    const res = await fetch('api/save_questionnaire.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(answers)
    });
    const json = await res.json();

    if (json.success) {
      container.innerHTML = `
        <h2>Vielen Dank, ${answers.firstname}!</h2>
        <p style="color:#a1a1a6">Wir melden uns so schnell wie möglich bei dir.</p>`;
      nextBtn.style.display = 'none';
      prevBtn.style.display = 'none';
      progress.textContent  = 'Abgeschlossen ✓';
    } else {
      alert('Fehler: ' + (json.error || 'Unbekannter Fehler'));
      nextBtn.disabled = false;
      nextBtn.textContent = 'Absenden';
    }
  } catch (e) {
    alert('Netzwerkfehler. Bitte versuche es später erneut.');
    nextBtn.disabled = false;
    nextBtn.textContent = 'Absenden';
  }
}

nextBtn.addEventListener("click", nextStep);
prevBtn.addEventListener("click", () => {
  if (step > 0) { step--; renderStep(); }
});

renderStep();
