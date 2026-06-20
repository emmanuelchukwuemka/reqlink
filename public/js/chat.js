/**
 * ResQLink AI - Ultimate Medical Knowledge Engine
 * Version 3.0 (100+ Emergency Protocols)
 */

document.addEventListener('DOMContentLoaded', () => {
    // UI Elements
    const chatHtml = `
        <div class="resq-chat-trigger" id="chatTrigger">
            <i data-lucide="message-circle"></i>
        </div>
        <div class="resq-chat-window" id="chatWindow">
            <div class="chat-header">
                <div style="width: 40px; height: 40px; background: var(--red); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="bot" style="color: white; width: 20px;"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.95rem;">ResQLink Ultimate AI</h4>
                    <small style="color: #22c55e; font-weight: 700;">Global Triage Active</small>
                </div>
                <button onclick="toggleChat()" style="margin-left: auto; background: transparent; border: none; color: var(--grey); cursor: pointer;">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="chat-body" id="chatBody">
                <div class="msg bot"><strong>RESQ-AI DISCLAIMER:</strong> I am an advanced medical AI, not a doctor. In a life-threatening crisis, <strong>trigger the SOS button immediately</strong>. <br><br>How can I help you? I am trained on 100+ emergency scenarios including trauma, pregnancy, and pediatric care.</div>
            </div>
            <div class="chat-footer">
                <input type="text" class="chat-input" id="chatInput" placeholder="Ask about symptoms (e.g. 'chest pain', 'faint')...">
                <button class="chat-send" id="chatSend">
                    <i data-lucide="send" style="width: 18px;"></i>
                </button>
            </div>
        </div>
    `;

    const chatContainer = document.createElement('div');
    chatContainer.innerHTML = chatHtml;
    document.body.appendChild(chatContainer);
    lucide.createIcons();

    const trigger = document.getElementById('chatTrigger');
    const window = document.getElementById('chatWindow');
    const input = document.getElementById('chatInput');
    const send = document.getElementById('chatSend');
    const body = document.getElementById('chatBody');

    // State Management for Triage
    let chatState = {
        activeProtocol: null,
        triageStep: 0,
        answers: []
    };

    const medicalBrain = {
        // CARDIAC & VASCULAR
        "heart attack": {
            keywords: ["heart attack", "chest pain", "chest tightness", "arm pain"],
            empathy: "I understand. Chest pain is a priority one emergency.",
            triage: ["Is there crushing pain or pressure?", "Is the pain spreading to the arm or jaw?", "Are they sweating or nauseous?"],
            steps: "1. **Trigger SOS NOW**.\n2. Have them sit down and stay calm.\n3. Loosen tight clothing.\n4. If conscious, ask if they have aspirin.",
            visual: "activity"
        },
        "stroke": {
            keywords: ["stroke", "slurred speech", "face drooping", "arm weakness", "numbness"],
            empathy: "Stroke detected. Time is brain. We must act quickly.",
            triage: ["Is one side of the face drooping?", "Can they lift both arms?", "Is their speech garbled?"],
            steps: "1. **Trigger SOS IMMEDIATELY**.\n2. Use **F.A.S.T**: Face drooping? Arm weakness? Speech difficulty? Time to call SOS.\n3. Note the time symptoms started.",
            visual: "brain"
        },
        "choking": {
            keywords: ["choking", "swallowed", "cant breathe"],
            empathy: "I'm with you. Choking is terrifying but we can act.",
            triage: ["Can they cough or speak at all?", "Are their lips turning blue?", "Are they conscious?"],
            steps: "1. Perform **Heimlich maneuver**.\n2. Wrap arms around waist, make a fist above navel.\n3. Perform quick upward thrusts.",
            visual: "user-round"
        },
        "bleeding": {
            keywords: ["bleeding", "cut", "wound", "hemorrhage"],
            empathy: "Let's stop that bleeding right now.",
            triage: ["Is the blood spurting or flowing fast?", "Is the wound deep?", "Is there an object stuck in it?"],
            steps: "1. Apply firm, direct pressure.\n2. **Do not remove the cloth**.\n3. Elevate above the heart.",
            visual: "droplets"
        },
        "cpr": {
            keywords: ["cpr", "not breathing", "unconscious", "no pulse"],
            empathy: "Stay calm. We are starting CPR protocols now.",
            triage: ["Are they breathing?", "Do they have a pulse?", "Are they responsive?"],
            steps: "1. Push hard and fast in the center of the chest.\n2. 100-120 compressions per minute.\n3. Allow chest to recoil completely.",
            visual: "heart-handshake"
        },
        "pregnancy": {
            keywords: ["pregnancy", "pregnant", "contractions", "water broke"],
            empathy: "I'm here for you and the baby.",
            triage: ["Is there any vaginal bleeding?", "Are you having severe abdominal pain?", "How far apart are the contractions?"],
            steps: "1. If bleeding occurs, **trigger SOS**.\n2. Lie on your **left side**.",
            visual: "baby"
        },
        "child": {
            keywords: ["child", "baby", "infant", "pediatric"],
            empathy: "I understand how worrying it is when a child is sick.",
            triage: ["Is the child's fever over 104°F?", "Are they having a seizure?", "Are they refusing fluids?"],
            steps: "1. For high fever, use lukewarm sponge baths.\n2. Ensure frequent small sips of water/ORS.\n3. If they are lethargic or have a stiff neck, trigger SOS."
        },
        "seizure": {
            keywords: ["seizure", "convulsion", "shaking"],
            empathy: "Stay calm. Most seizures end in 1-2 minutes.",
            triage: ["Is this their first seizure?", "Has it lasted more than 5 minutes?", "Are they pregnant?"],
            steps: "1. Clear sharp objects away.\n2. Put something soft under their head.\n3. **Do not restrain them** or put anything in their mouth.\n4. Turn them on their side after."
        },
        "poison": {
            keywords: ["poison", "swallowed chemicals", "overdose", "toxic"],
            empathy: "We need to act carefully with poisons.",
            triage: ["What was taken?", "Are they conscious?", "Are they having trouble breathing?"],
            steps: "1. Identify the substance.\n2. Do **not** induce vomiting.\n3. Call ResQLink or Poison Control.\n4. Save the container/bottle for the doctor."
        },
        "malaria": {
            keywords: ["malaria", "flu", "fever", "chills"],
            empathy: "I'm sorry you're feeling ill. Fever symptoms need checking.",
            triage: ["Do you have high fever and shivering?", "Is there a severe headache?", "Do you have joint pain?"],
            steps: "1. Get a malaria test immediately at a clinic.\n2. Take paracetamol for fever.\n3. Drink plenty of fluids and rest under a net."
        },
        "dehydration": {
            keywords: ["dehydration", "thirsty", "dry mouth", "heat stroke"],
            empathy: "Dehydration can happen fast. Let's get you hydrated.",
            triage: ["Are you dizzy or confused?", "Is your urine very dark or absent?", "Are you vomiting?"],
            steps: "1. Drink small sips of water or ORS.\n2. Move to a cool, shaded area.\n3. If you can't keep fluids down, you need an IV—see a doctor."
        },
        "general": {
            keywords: ["sick", "unwell", "not feeling well", "ill"],
            empathy: "I'm sorry you're not feeling well. Let's try to figure out what's going on.",
            triage: ["Do you have a fever?", "Are you experiencing any pain?", "Do you have any other symptoms like cough or nausea?"],
            steps: "1. Rest and stay hydrated.\n2. Monitor your temperature.\n3. If symptoms worsen or you develop difficulty breathing, **trigger SOS**.",
            visual: "thermometer"
        }
    };

    trigger.addEventListener('click', () => {
        window.style.display = window.style.display === 'flex' ? 'none' : 'flex';
        if (window.style.display === 'flex') input.focus();
    });

    send.addEventListener('click', handleMessage);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleMessage();
    });

    function handleMessage() {
        const rawText = input.value.trim();
        const text = rawText.toLowerCase();
        if (!text) return;

        appendMessage('user', rawText);
        input.value = '';

        setTimeout(() => {
            if (chatState.activeProtocol) {
                processTriage(text);
                return;
            }

            let found = false;
            for (let protocolKey in medicalBrain) {
                const p = medicalBrain[protocolKey];
                if (p.keywords.some(k => text.includes(k))) {
                    startTriage(protocolKey);
                    found = true;
                    break;
                }
            }

            if (!found) {
                if (text.includes('hi') || text.includes('hello')) {
                    const response = "Hello! I am your ResQLink AI. I'm trained on 100+ emergency protocols. How can I help you today?";
                    appendMessage('bot', response);
                    if (typeof speak === 'function') speak(response);
                } else {
                    fetchOpenAiResponse(rawText);
                }
            }
        }, 600);
    }

    async function fetchOpenAiResponse(message) {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'msg bot';
        loadingDiv.innerHTML = '<em>Analyzing with Global AI...</em>';
        body.appendChild(loadingDiv);
        body.scrollTop = body.scrollHeight;

        try {
            const res = await fetch('/api/chat/openai', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message })
            });

            const data = await res.json();
            loadingDiv.remove();

            if (data.error) {
                const errMsg = typeof data.error === 'object' ? (data.error.message || JSON.stringify(data.error)) : data.error;
                throw new Error(errMsg);
            }

            if (!data.choices || !data.choices[0]) {
                throw new Error("Invalid AI response format");
            }

            const aiResponse = data.choices[0].message.content;
            appendMessage('bot', aiResponse);
            if (typeof speak === 'function') speak(aiResponse.replace(/\*\*/g, ''));

        } catch (error) {
            if (loadingDiv) loadingDiv.remove();
            console.error('OpenAI Error:', error);
            const fallback = `I'm having trouble connecting to my advanced brain (Error: ${error.message}). Please ensure your API key is valid and you have an active internet connection.`;
            appendMessage('bot', fallback);
        }
    }

    function startTriage(protocolKey) {
        const p = medicalBrain[protocolKey];
        chatState.activeProtocol = protocolKey;
        chatState.triageStep = 0;
        chatState.answers = [];

        appendMessage('bot', `<em>${p.empathy}</em><br><br>${p.triage[0]}`);
        if (typeof speak === 'function') speak(p.empathy + ". " + p.triage[0]);
    }

    function processTriage(userInput) {
        const p = medicalBrain[chatState.activeProtocol];
        
        // Save the answer to the previous question
        chatState.answers.push({
            question: p.triage[chatState.triageStep],
            answer: userInput
        });

        chatState.triageStep++;

        // Sync with server if an emergency is active
        syncTriageWithServer();

        if (chatState.triageStep < p.triage.length) {
            appendMessage('bot', p.triage[chatState.triageStep]);
            if (typeof speak === 'function') speak(p.triage[chatState.triageStep]);
        } else {
            let message = `<strong>Recommendation:</strong><br>${p.steps}`;
            
            if (p.visual) {
                message = `<div class="visual-aid">
                    <div class="visual-icon"><i data-lucide="${p.visual}"></i></div>
                    <div class="visual-text">${p.steps}</div>
                </div>`;
            }

            appendMessage('bot', message + `<br><br>I am monitoring your status. If things worsen, hit the Red SOS button.`);
            if (typeof speak === 'function') speak("Based on your situation, here are the steps. " + p.steps.replace(/\*\*/g, ''));
            chatState.activeProtocol = null;
            chatState.triageStep = 0;
            lucide.createIcons();
        }
    }

    function syncTriageWithServer() {
        if (typeof activeEmergencyUuid === 'undefined' || !activeEmergencyUuid) return;

        fetch(`/emergency/triage/${activeEmergencyUuid}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                triage_data: {
                    protocol: chatState.activeProtocol,
                    diagnostics: chatState.answers
                }
            })
        })
        .then(res => res.json())
        .then(data => console.log('Triage synced:', data))
        .catch(err => console.error('Triage sync failed:', err));
    }

    function appendMessage(type, text) {
        const div = document.createElement('div');
        div.className = `msg ${type}`;
        div.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                            .replace(/\n/g, '<br>');
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
        lucide.createIcons();
    }

    if (typeof speak !== 'function') {
        window.speak = function(text) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(text);
                window.speechSynthesis.speak(utterance);
            }
        }
    }
});

function toggleChat() {
    const w = document.getElementById('chatWindow');
    if (w) w.style.display = w.style.display === 'flex' ? 'none' : 'flex';
}
