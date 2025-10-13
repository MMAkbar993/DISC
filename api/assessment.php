<?php
/**
 * Assessment API Endpoints
 * DISC Assessment Platform
 */

require_once '../config/config.php';
require_once '../config/database.php';

setCorsHeaders();

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($method) {
        case 'POST':
            if (isset($input['action'])) {
                switch ($input['action']) {
                    case 'start_assessment':
                        startAssessment($db, $input);
                        break;
                    case 'submit_assessment':
                        submitAssessment($db, $input);
                        break;
                    case 'get_questions':
                        getQuestions($db);
                        break;
                    default:
                        sendError('Invalid action');
                }
            } else {
                sendError('Action required');
            }
            break;
        case 'GET':
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'get_results':
                        getResults($db);
                        break;
                    default:
                        sendError('Invalid action');
                }
            } else {
                sendError('Action required');
            }
            break;
        default:
            sendError('Method not allowed', 405);
    }
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
}

/**
 * Start assessment - validate access code and create participant record
 */
function startAssessment($db, $input) {
    $fullName = sanitizeInput($input['full_name'] ?? '');
    $position = sanitizeInput($input['position'] ?? '');
    $email = sanitizeInput($input['email'] ?? '');
    $accessCode = sanitizeInput($input['access_code'] ?? '');
    
    if (empty($fullName) || empty($accessCode)) {
        sendError('Full name and access code are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError('Valid email address is required');
    }

    try {
        $db->beginTransaction();

        // Validate and reserve access code
        $stmt = $db->prepare("
            SELECT id, code, is_used 
            FROM access_codes 
            WHERE code = ? AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$accessCode]);
        $code = $stmt->fetch();

        if (!$code) {
            sendError('Invalid or expired access code');
        }

        if ($code['is_used']) {
            sendError('Access code has already been used');
        }

        // Create participant record
        $stmt = $db->prepare("
            INSERT INTO participants (full_name, position, email, access_code, started_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$fullName, $position, $email, $accessCode]);
        $participantId = $db->lastInsertId();

        // Mark access code as used
        $stmt = $db->prepare("
            UPDATE access_codes 
            SET is_used = TRUE, used_by = ?, used_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$fullName, $code['id']]);

        $db->commit();

        // Store participant ID in session
        $_SESSION['participant_id'] = $participantId;
        $_SESSION['participant_name'] = $fullName;

        sendSuccess([
            'participant_id' => $participantId,
            'full_name' => $fullName,
            'message' => 'Assessment started successfully'
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        sendError('Database error');
    }
}

/**
 * Get assessment questions
 */
function getQuestions($db) {
    // Static questions data (same as frontend)
    $questions = [
        [
            'id' => 1,
            'question' => 'In a team meeting, I tend to:',
            'options' => [
                'Take charge and direct the discussion',
                'Encourage participation and build enthusiasm',
                'Listen carefully and support others\' ideas',
                'Ask detailed questions and analyze proposals'
            ]
        ],
        [
            'id' => 2,
            'question' => 'When facing a deadline, I:',
            'options' => [
                'Push hard to get results quickly',
                'Rally the team with positive energy',
                'Work steadily and support team members',
                'Focus on accuracy and quality standards'
            ]
        ],
        [
            'id' => 3,
            'question' => 'My communication style is:',
            'options' => [
                'Direct and to the point',
                'Enthusiastic and expressive',
                'Warm and supportive',
                'Factual and precise'
            ]
        ],
        [
            'id' => 4,
            'question' => 'When making decisions, I:',
            'options' => [
                'Make them quickly based on gut instinct',
                'Consider how they\'ll affect team morale',
                'Take time to ensure everyone is comfortable',
                'Analyze all available data thoroughly'
            ]
        ],
        [
            'id' => 5,
            'question' => 'In conflict situations, I:',
            'options' => [
                'Address issues head-on and resolve quickly',
                'Try to lighten the mood and find common ground',
                'Mediate patiently and seek harmony',
                'Gather facts and find logical solutions'
            ]
        ],
        [
            'id' => 6,
            'question' => 'My ideal work environment is:',
            'options' => [
                'Fast-paced with clear goals and autonomy',
                'Collaborative and socially engaging',
                'Stable and supportive with clear processes',
                'Organized and detail-oriented'
            ]
        ],
        [
            'id' => 7,
            'question' => 'People would describe me as:',
            'options' => [
                'Assertive and competitive',
                'Optimistic and persuasive',
                'Patient and reliable',
                'Precise and systematic'
            ]
        ],
        [
            'id' => 8,
            'question' => 'When starting a new project, I first:',
            'options' => [
                'Set clear goals and deadlines',
                'Get everyone excited about the vision',
                'Ensure the team has proper support',
                'Create detailed plans and procedures'
            ]
        ],
        [
            'id' => 9,
            'question' => 'Under pressure, I:',
            'options' => [
                'Thrive and become more focused',
                'Stay positive and motivate others',
                'Remain calm and steady',
                'Become more careful and methodical'
            ]
        ],
        [
            'id' => 10,
            'question' => 'When delegating tasks, I:',
            'options' => [
                'Give clear expectations and check results',
                'Inspire people and trust them to deliver',
                'Provide ongoing support and guidance',
                'Give detailed instructions and specifications'
            ]
        ],
        [
            'id' => 11,
            'question' => 'I prefer feedback that is:',
            'options' => [
                'Direct and results-focused',
                'Positive and encouraging',
                'Gentle and constructive',
                'Specific and data-driven'
            ]
        ],
        [
            'id' => 12,
            'question' => 'In group settings, I:',
            'options' => [
                'Take charge and drive action',
                'Engage everyone and build energy',
                'Support others and maintain harmony',
                'Focus on accuracy and quality'
            ]
        ],
        [
            'id' => 13,
            'question' => 'My biggest fear in leadership is:',
            'options' => [
                'Being seen as weak or indecisive',
                'Being rejected or losing popularity',
                'Creating conflict or instability',
                'Making mistakes or being wrong'
            ]
        ],
        [
            'id' => 14,
            'question' => 'When learning new skills, I prefer to:',
            'options' => [
                'Jump in and learn by doing',
                'Learn with others in a social setting',
                'Take my time and practice gradually',
                'Study thoroughly before attempting'
            ]
        ],
        [
            'id' => 15,
            'question' => 'My approach to risk is:',
            'options' => [
                'Embrace calculated risks for big rewards',
                'Take risks if they seem exciting',
                'Prefer stability and avoid unnecessary risks',
                'Carefully analyze risks before proceeding'
            ]
        ],
        [
            'id' => 16,
            'question' => 'When motivating others, I:',
            'options' => [
                'Challenge them to achieve more',
                'Inspire them with vision and enthusiasm',
                'Encourage and support their efforts',
                'Provide clear standards and recognition'
            ]
        ],
        [
            'id' => 17,
            'question' => 'My time management style is:',
            'options' => [
                'Focus on high-priority tasks first',
                'Balance tasks with social interactions',
                'Work steadily through my to-do list',
                'Plan and organize everything in detail'
            ]
        ],
        [
            'id' => 18,
            'question' => 'When presenting ideas, I:',
            'options' => [
                'Focus on bottom-line results',
                'Make it engaging and memorable',
                'Ensure everyone understands and agrees',
                'Provide comprehensive data and analysis'
            ]
        ],
        [
            'id' => 19,
            'question' => 'My leadership strength is:',
            'options' => [
                'Getting results and driving performance',
                'Building relationships and team spirit',
                'Creating stability and supporting others',
                'Ensuring quality and accuracy'
            ]
        ],
        [
            'id' => 20,
            'question' => 'When facing change, I:',
            'options' => [
                'Embrace it as an opportunity',
                'Get excited about new possibilities',
                'Need time to adjust and adapt',
                'Want to understand all implications first'
            ]
        ],
        [
            'id' => 21,
            'question' => 'In problem-solving, I:',
            'options' => [
                'Act quickly to find solutions',
                'Brainstorm creative alternatives with others',
                'Consider impact on all team members',
                'Research thoroughly before deciding'
            ]
        ],
        [
            'id' => 22,
            'question' => 'My preferred meeting style is:',
            'options' => [
                'Short, focused, and action-oriented',
                'Interactive with lots of discussion',
                'Collaborative with everyone\'s input',
                'Well-structured with detailed agendas'
            ]
        ],
        [
            'id' => 23,
            'question' => 'When giving feedback, I:',
            'options' => [
                'Get straight to the point',
                'Focus on positive aspects first',
                'Consider the person\'s feelings',
                'Provide specific examples and data'
            ]
        ],
        [
            'id' => 24,
            'question' => 'My work pace is typically:',
            'options' => [
                'Fast and results-driven',
                'Energetic with bursts of activity',
                'Steady and consistent',
                'Methodical and thorough'
            ]
        ]
    ];

    sendSuccess(['questions' => $questions]);
}

/**
 * Submit assessment answers and calculate DISC profile
 */
function submitAssessment($db, $input) {
    // Check if participant is logged in
    if (!isset($_SESSION['participant_id'])) {
        sendError('Assessment session not found', 401);
    }

    $participantId = $_SESSION['participant_id'];
    $answers = $input['answers'] ?? [];

    if (empty($answers) || count($answers) !== TOTAL_QUESTIONS) {
        sendError('All questions must be answered');
    }

    try {
        $db->beginTransaction();

        // Save answers to database
        $stmt = $db->prepare("
            INSERT INTO assessment_answers (participant_id, question_number, most_choice, least_choice) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($answers as $questionNum => $answer) {
            if (!isset($answer['most']) || !isset($answer['least'])) {
                throw new Exception('Invalid answer format for question ' . $questionNum);
            }
            
            $stmt->execute([
                $participantId,
                $questionNum + 1,
                $answer['most'],
                $answer['least']
            ]);
        }

        // Calculate DISC scores
        $discScores = calculateDISCScores($answers);
        
        // Determine primary and secondary types
        $types = determineDISCTypes($discScores);
        
        // Save DISC profile
        $stmt = $db->prepare("
            INSERT INTO disc_profiles (
                participant_id, dominance_score, influence_score, steadiness_score, compliance_score,
                primary_type, secondary_type, profile_title, profile_description, strengths,
                areas_for_development, leadership_style
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $profileData = generateProfileData($types, $discScores);
        
        $stmt->execute([
            $participantId,
            $discScores['D'],
            $discScores['I'],
            $discScores['S'],
            $discScores['C'],
            $types['primary'],
            $types['secondary'],
            $profileData['title'],
            $profileData['description'],
            $profileData['strengths'],
            $profileData['areas_for_development'],
            $profileData['leadership_style']
        ]);

        // Mark assessment as completed
        $stmt = $db->prepare("
            UPDATE participants 
            SET assessment_completed = TRUE, completed_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$participantId]);

        $db->commit();

        sendSuccess([
            'profile' => [
                'primary_type' => $types['primary'],
                'secondary_type' => $types['secondary'],
                'scores' => $discScores,
                'title' => $profileData['title'],
                'description' => $profileData['description']
            ],
            'message' => 'Assessment completed successfully'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        sendError('Error processing assessment: ' . $e->getMessage());
    }
}

/**
 * Calculate DISC scores from answers
 */
function calculateDISCScores($answers) {
    $scores = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
    
    // DISC scoring patterns for each question
    $scoringPatterns = [
        // Question 1: D=0,1 I=1,3 S=2,3 C=0,2
        [0, 1, 3, 2], [1, 3, 2, 0], [2, 3, 0, 1], [3, 2, 1, 0],
        // Question 2: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 3: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 4: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 5: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 6: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 7: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 8: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 9: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 10: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 11: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 12: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 13: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 14: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 15: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 16: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 17: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 18: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 19: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 20: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 21: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 22: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 23: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2],
        // Question 24: D=0,1 I=1,2 S=2,3 C=0,3
        [0, 1, 2, 3], [1, 2, 3, 0], [2, 3, 0, 1], [3, 0, 1, 2]
    ];

    foreach ($answers as $questionIndex => $answer) {
        $pattern = $scoringPatterns[$questionIndex];
        $mostChoice = $answer['most'];
        $leastChoice = $answer['least'];
        
        // Award points for most choice
        if (isset($pattern[$mostChoice])) {
            $scores[['D', 'I', 'S', 'C'][$pattern[$mostChoice]]]++;
        }
        
        // Deduct points for least choice
        if (isset($pattern[$leastChoice])) {
            $scores[['D', 'I', 'S', 'C'][$pattern[$leastChoice]]]--;
        }
    }

    return $scores;
}

/**
 * Determine primary and secondary DISC types
 */
function determineDISCTypes($scores) {
    $sorted = $scores;
    arsort($sorted);
    $types = array_keys($sorted);
    
    return [
        'primary' => $types[0],
        'secondary' => $types[1]
    ];
}

/**
 * Generate profile data based on DISC types
 */
function generateProfileData($types, $scores) {
    $primary = $types['primary'];
    $secondary = $types['secondary'];
    
    $profiles = [
        'D' => [
            'title' => 'Dominant Leader',
            'description' => 'You are a results-oriented leader who takes charge and drives for success.',
            'strengths' => 'Decisive, Direct, Goal-oriented, Competitive, Confident',
            'areas_for_development' => 'Patience, Listening skills, Delegation, Team collaboration',
            'leadership_style' => 'Direct and results-focused leadership approach'
        ],
        'I' => [
            'title' => 'Influential Leader',
            'description' => 'You are an enthusiastic leader who inspires and motivates others.',
            'strengths' => 'Enthusiastic, Persuasive, Optimistic, Relationship-building, Inspiring',
            'areas_for_development' => 'Attention to detail, Follow-through, Structured planning',
            'leadership_style' => 'Inspiring and relationship-focused leadership approach'
        ],
        'S' => [
            'title' => 'Steady Leader',
            'description' => 'You are a supportive leader who values stability and team harmony.',
            'strengths' => 'Supportive, Patient, Reliable, Team-oriented, Stable',
            'areas_for_development' => 'Assertiveness, Change management, Risk-taking',
            'leadership_style' => 'Supportive and team-focused leadership approach'
        ],
        'C' => [
            'title' => 'Conscientious Leader',
            'description' => 'You are a systematic leader who values quality and accuracy.',
            'strengths' => 'Analytical, Systematic, Quality-focused, Detail-oriented, Accurate',
            'areas_for_development' => 'Flexibility, Speed of decision-making, People skills',
            'leadership_style' => 'Systematic and quality-focused leadership approach'
        ]
    ];
    
    return $profiles[$primary];
}

/**
 * Get assessment results
 */
function getResults($db) {
    if (!isset($_SESSION['participant_id'])) {
        sendError('Assessment session not found', 401);
    }

    try {
        $stmt = $db->prepare("
            SELECT p.*, dp.* 
            FROM participants p 
            LEFT JOIN disc_profiles dp ON p.id = dp.participant_id 
            WHERE p.id = ?
        ");
        $stmt->execute([$_SESSION['participant_id']]);
        $result = $stmt->fetch();

        if (!$result) {
            sendError('Results not found');
        }

        sendSuccess(['results' => $result]);

    } catch (PDOException $e) {
        sendError('Database error');
    }
}
?>
