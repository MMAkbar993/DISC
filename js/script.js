
// DISC Assessment Questions with 4 options each
const questions = [
    {
        question: "In a team meeting, I tend to:",
        options: [
            "Take charge and direct the discussion",
            "Encourage participation and build enthusiasm",
            "Listen carefully and support others' ideas",
            "Ask detailed questions and analyze proposals"
        ]
    },
    {
        question: "When facing a deadline, I:",
        options: [
            "Push hard to get results quickly",
            "Rally the team with positive energy",
            "Work steadily and support team members",
            "Focus on accuracy and quality standards"
        ]
    },
    {
        question: "My communication style is:",
        options: [
            "Direct and to the point",
            "Enthusiastic and expressive",
            "Warm and supportive",
            "Factual and precise"
        ]
    },
    {
        question: "When making decisions, I:",
        options: [
            "Make them quickly based on gut instinct",
            "Consider how they'll affect team morale",
            "Take time to ensure everyone is comfortable",
            "Analyze all available data thoroughly"
        ]
    },
    {
        question: "In conflict situations, I:",
        options: [
            "Address issues head-on and resolve quickly",
            "Try to lighten the mood and find common ground",
            "Mediate patiently and seek harmony",
            "Gather facts and find logical solutions"
        ]
    },
    {
        question: "My ideal work environment is:",
        options: [
            "Fast-paced with clear goals and autonomy",
            "Collaborative and socially engaging",
            "Stable and supportive with clear processes",
            "Organized and detail-oriented"
        ]
    },
    {
        question: "People would describe me as:",
        options: [
            "Assertive and competitive",
            "Optimistic and persuasive",
            "Patient and reliable",
            "Precise and systematic"
        ]
    },
    {
        question: "When starting a new project, I first:",
        options: [
            "Set clear goals and deadlines",
            "Get everyone excited about the vision",
            "Ensure the team has proper support",
            "Create detailed plans and procedures"
        ]
    },
    {
        question: "Under pressure, I:",
        options: [
            "Thrive and become more focused",
            "Stay positive and motivate others",
            "Remain calm and steady",
            "Become more careful and methodical"
        ]
    },
    {
        question: "When delegating tasks, I:",
        options: [
            "Give clear expectations and check results",
            "Inspire people and trust them to deliver",
            "Provide ongoing support and guidance",
            "Give detailed instructions and specifications"
        ]
    },
    {
        question: "I prefer feedback that is:",
        options: [
            "Direct and results-focused",
            "Positive and encouraging",
            "Gentle and constructive",
            "Specific and data-driven"
        ]
    },
    {
        question: "In group settings, I:",
        options: [
            "Take charge and drive action",
            "Engage everyone and build energy",
            "Support others and maintain harmony",
            "Focus on accuracy and quality"
        ]
    },
    {
        question: "My biggest fear in leadership is:",
        options: [
            "Being seen as weak or indecisive",
            "Being rejected or losing popularity",
            "Creating conflict or instability",
            "Making mistakes or being wrong"
        ]
    },
    {
        question: "When learning new skills, I prefer to:",
        options: [
            "Jump in and learn by doing",
            "Learn with others in a social setting",
            "Take my time and practice gradually",
            "Study thoroughly before attempting"
        ]
    },
    {
        question: "My approach to risk is:",
        options: [
            "Embrace calculated risks for big rewards",
            "Take risks if they seem exciting",
            "Prefer stability and avoid unnecessary risks",
            "Carefully analyze risks before proceeding"
        ]
    },
    {
        question: "When motivating others, I:",
        options: [
            "Challenge them to achieve more",
            "Inspire them with vision and enthusiasm",
            "Encourage and support their efforts",
            "Provide clear standards and recognition"
        ]
    },
    {
        question: "My time management style is:",
        options: [
            "Focus on high-priority tasks first",
            "Balance tasks with social interactions",
            "Work steadily through my to-do list",
            "Plan and organize everything in detail"
        ]
    },
    {
        question: "When presenting ideas, I:",
        options: [
            "Focus on bottom-line results",
            "Make it engaging and memorable",
            "Ensure everyone understands and agrees",
            "Provide comprehensive data and analysis"
        ]
    },
    {
        question: "My leadership strength is:",
        options: [
            "Getting results and driving performance",
            "Building relationships and team spirit",
            "Creating stability and supporting others",
            "Ensuring quality and accuracy"
        ]
    },
    {
        question: "When facing change, I:",
        options: [
            "Embrace it as an opportunity",
            "Get excited about new possibilities",
            "Need time to adjust and adapt",
            "Want to understand all implications first"
        ]
    },
    {
        question: "In problem-solving, I:",
        options: [
            "Act quickly to find solutions",
            "Brainstorm creative alternatives with others",
            "Consider impact on all team members",
            "Research thoroughly before deciding"
        ]
    },
    {
        question: "My preferred meeting style is:",
        options: [
            "Short, focused, and action-oriented",
            "Interactive with lots of discussion",
            "Collaborative with everyone's input",
            "Well-structured with detailed agendas"
        ]
    },
    {
        question: "When giving feedback, I:",
        options: [
            "Get straight to the point",
            "Focus on positive aspects first",
            "Consider the person's feelings",
            "Provide specific examples and data"
        ]
    },
    {
        question: "My work pace is typically:",
        options: [
            "Fast and results-driven",
            "Energetic with bursts of activity",
            "Steady and consistent",
            "Methodical and thorough"
        ]
    }
];

// Assessment state
let currentAnswers = {};
let userCredentials = {};
let currentQuestionIndex = 0;

// Initialize the application
document.addEventListener('DOMContentLoaded', function () {
    setupLoginForm();
    generateQuestions();
    setupNavigation();
    setupSubmitButton();
});

// Setup login form
function setupLoginForm() {
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const accessCode = document.getElementById('accessCode').value.trim();

        if (username && accessCode) {
            // Store user credentials for later backend integration
            userCredentials = { username, accessCode };

            // TODO: Add backend authentication call here
            // Example: authenticateUser(username, accessCode)

            showAssessment();
        } else {
            alert('Please fill in all required fields.');
        }
    });
}

// Show assessment section
function showAssessment() {
    document.getElementById('loginSection').style.display = 'none';
    document.getElementById('assessmentSection').style.display = 'block';
    updateProgress();
}

// Generate question cards
function generateQuestions() {
    const container = document.getElementById('questionsContainer');

    questions.forEach((questionData, index) => {
        const questionCard = document.createElement('div');
        questionCard.className = 'question-card';
        questionCard.id = `question-${index}`;

        let optionsHTML = '';
        questionData.options.forEach((option, optionIndex) => {
            optionsHTML += `
                        <div class="option-row">
                            <div class="option-text">${option}</div>
                            <button class="option-button most" data-question="${index}" data-option="${optionIndex}" data-type="most">
                                Most
                            </button>
                            <button class="option-button least" data-question="${index}" data-option="${optionIndex}" data-type="least">
                                Least
                            </button>
                        </div>
                    `;
        });

        questionCard.innerHTML = `
                    <div class="question-number">Question ${index + 1} of 24</div>
                    <div class="question-text">${questionData.question}</div>
                    <div class="options-container">
                        ${optionsHTML}
                    </div>
                `;

        container.appendChild(questionCard);
    });

    // Show first question
    showQuestion(0);

    // Add click handlers for option buttons
    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('option-button')) {
            handleOptionClick(e.target);
        }
    });
}

// Setup navigation
function setupNavigation() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    prevBtn.addEventListener('click', function () {
        if (currentQuestionIndex > 0) {
            showQuestion(currentQuestionIndex - 1);
        }
    });

    nextBtn.addEventListener('click', function () {
        if (currentQuestionIndex < questions.length - 1 && isCurrentQuestionComplete()) {
            showQuestion(currentQuestionIndex + 1);
        }
    });
}

// Show specific question
function showQuestion(index) {
    // Hide all questions
    const allQuestions = document.querySelectorAll('.question-card');
    allQuestions.forEach(card => card.classList.remove('active'));

    // Show current question
    const currentQuestion = document.getElementById(`question-${index}`);
    if (currentQuestion) {
        currentQuestion.classList.add('active');
        currentQuestionIndex = index;

        // Update navigation buttons
        updateNavigationButtons();

        // Update question status
        document.getElementById('questionStatus').textContent =
            `Question ${index + 1} of ${questions.length}`;
    }
}

// Check if current question is complete
function isCurrentQuestionComplete() {
    return currentAnswers[currentQuestionIndex] &&
        currentAnswers[currentQuestionIndex].most !== undefined &&
        currentAnswers[currentQuestionIndex].least !== undefined;
}

// Update navigation buttons
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitContainer = document.querySelector('.submit-container');

    // Previous button
    prevBtn.disabled = currentQuestionIndex === 0;

    // Next button
    if (currentQuestionIndex === questions.length - 1) {
        nextBtn.style.display = 'none';
        submitContainer.style.display = 'block';
    } else {
        nextBtn.style.display = 'block';
        submitContainer.style.display = 'none';
        nextBtn.disabled = !isCurrentQuestionComplete();
    }
}

// Handle option button clicks
function handleOptionClick(button) {
    const questionIndex = parseInt(button.dataset.question);
    const optionIndex = parseInt(button.dataset.option);
    const optionType = button.dataset.type;
    const questionCard = button.closest('.question-card');

    // Initialize question answers if not exists
    if (!currentAnswers[questionIndex]) {
        currentAnswers[questionIndex] = {};
    }

    // Check if this option is already selected for the opposite type
    const oppositeType = optionType === 'most' ? 'least' : 'most';
    if (currentAnswers[questionIndex][oppositeType] === optionIndex) {
        // Show error message and prevent selection
        showValidationError(`You cannot select the same option for both MOST and LEAST. Please choose different options.`);
        return;
    }

    // Remove previous selections for this question and option type
    const sameTypeButtons = questionCard.querySelectorAll(`.option-button.${optionType}`);
    sameTypeButtons.forEach(btn => btn.classList.remove('selected'));

    // Add selection to clicked button
    button.classList.add('selected');

    // Store the answer
    currentAnswers[questionIndex][optionType] = optionIndex;

    // Clear any previous validation errors
    clearValidationError();

    // Update navigation and progress
    updateNavigationButtons();
    updateProgress();
}

// Update progress bar and submit button
function updateProgress() {
    let fullyAnsweredCount = 0;
    const totalQuestions = questions.length;

    // Count questions that have both MOST and LEAST answers
    for (let i = 0; i < totalQuestions; i++) {
        if (currentAnswers[i] &&
            currentAnswers[i].most !== undefined &&
            currentAnswers[i].least !== undefined) {
            fullyAnsweredCount++;
        }
    }

    const progressPercentage = (fullyAnsweredCount / totalQuestions) * 100;

    // Update progress bar
    document.getElementById('progressFill').style.width = progressPercentage + '%';
    document.getElementById('progressText').textContent =
        `${fullyAnsweredCount} of ${totalQuestions} questions completed`;

    // Enable/disable submit button
    const submitBtn = document.getElementById('submitBtn');
    if (fullyAnsweredCount === totalQuestions) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Assessment';
    } else {
        submitBtn.disabled = true;
        submitBtn.textContent = `Complete ${totalQuestions - fullyAnsweredCount} more questions`;
    }
}

// Setup submit button
function setupSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.addEventListener('click', function () {
        if (Object.keys(currentAnswers).length === questions.length) {
            submitAssessment();
        }
    });
}

// Submit assessment
function submitAssessment() {
    // Validate all questions have both MOST and LEAST answers
    let fullyAnsweredCount = 0;
    for (let i = 0; i < questions.length; i++) {
        if (currentAnswers[i] &&
            currentAnswers[i].most !== undefined &&
            currentAnswers[i].least !== undefined) {
            fullyAnsweredCount++;
        }
    }

    if (fullyAnsweredCount !== questions.length) {
        alert('Please answer both MOST and LEAST for all questions before submitting.');
        return;
    }

    // Prepare assessment data for backend
    const assessmentData = {
        user: userCredentials,
        answers: currentAnswers,
        timestamp: new Date().toISOString(),
        questionsTotal: questions.length
    };

    // TODO: Replace with actual backend API call
    // Example: submitAnswers(assessmentData)
    console.log('Assessment data ready for backend:', assessmentData);

    // Simulate API call with loading state
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;

    // Simulate network delay
    setTimeout(() => {
        showCompletionMessage();
    }, 1500);
}

// Show validation error message
function showValidationError(message) {
    // Remove any existing error messages
    clearValidationError();
    
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.id = 'validation-error';
    errorDiv.className = 'validation-error';
    errorDiv.textContent = message;
    
    // Insert error message after the current question
    const currentQuestion = document.querySelector('.question-card.active');
    if (currentQuestion) {
        currentQuestion.appendChild(errorDiv);
        
        // Auto-remove error after 5 seconds
        setTimeout(() => {
            clearValidationError();
        }, 5000);
    }
}

// Clear validation error message
function clearValidationError() {
    const existingError = document.getElementById('validation-error');
    if (existingError) {
        existingError.remove();
    }
}

// Show completion message
function showCompletionMessage() {
    document.getElementById('assessmentSection').style.display = 'none';
    document.getElementById('completionSection').style.display = 'block';

    // TODO: Add backend call to process results
    // Example: processAssessmentResults(userCredentials.username)
}

// Contact Administrator Function
function contactAdministrator() {
    // Redirect to the contact page for enterprise inquiries
    window.location.href = 'contact.html';
}

// Backend Integration Placeholder Functions
// TODO: Implement these functions with actual API calls

/**
 * Authenticate user credentials
 * @param {string} username - User's username
 * @param {string} accessCode - User's access code
 * @returns {Promise} - Authentication result
 */
function authenticateUser(username, accessCode) {
    // TODO: Implement backend authentication
    // return fetch('/api/auth', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify({ username, accessCode })
    // });
}

/**
 * Submit assessment answers to backend
 * @param {Object} assessmentData - Complete assessment data
 * @returns {Promise} - Submission result
 */
function submitAnswers(assessmentData) {
    // TODO: Implement backend submission
    // return fetch('/api/assessment/submit', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify(assessmentData)
    // });
}

/**
 * Process assessment results
 * @param {string} username - User's username
 * @returns {Promise} - Processing result
 */
function processAssessmentResults(username) {
    // TODO: Implement results processing
    // return fetch(`/api/assessment/results/${username}`, {
    //     method: 'GET',
    //     headers: { 'Content-Type': 'application/json' }
    // });
}



(function () { function c() { var b = a.contentDocument || a.contentWindow.document; if (b) { var d = b.createElement('script'); d.innerHTML = "window.__CF$cv$params={r:'98b42255328cb4d0',t:'MTc1OTkxMDczNi4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);"; b.getElementsByTagName('head')[0].appendChild(d) } } if (document.body) { var a = document.createElement('iframe'); a.height = 1; a.width = 1; a.style.position = 'absolute'; a.style.top = 0; a.style.left = 0; a.style.border = 'none'; a.style.visibility = 'hidden'; document.body.appendChild(a); if ('loading' !== document.readyState) c(); else if (window.addEventListener) document.addEventListener('DOMContentLoaded', c); else { var e = document.onreadystatechange || function () { }; document.onreadystatechange = function (b) { e(b); 'loading' !== document.readyState && (document.onreadystatechange = e, c()) } } } })();

