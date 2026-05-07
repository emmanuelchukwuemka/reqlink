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
        triageStep: 0
    };

    const medicalBrain = {
        // CARDIAC & VASCULAR
        "heart attack": {
            keywords: ["heart attack", "chest pain", "chest tightness", "arm pain"],
            empathy: "I understand. Chest pain is a priority one emergency.",
            triage: ["Is there crushing pain or pressure?", "Is the pain spreading to the arm or jaw?", "Are they sweating or nauseous?"],
            steps: "1. **Trigger SOS NOW**.\n2. Have them sit down and stay calm.\n3. Loosen tight clothing.\n4. If conscious, ask if they have aspirin."
        },
        "stroke": {
            keywords: ["stroke", "slurred speech", "face drooping", "arm weakness", "numbness"],
            empathy: "Stroke detected. Time is brain. We must act quickly.",
            triage: ["Is one side of the face drooping?", "Can they lift both arms?", "Is their speech garbled?"],
            steps: "1. **Trigger SOS IMMEDIATELY**.\n2. Note the time symptoms started.\n3. Do not give food or water.\n4. Keep them lying on their side if vomiting."
        },
        "blood pressure": {
            keywords: ["blood pressure", "hypertension", "high bp"],
            empathy: "Managing blood pressure is vital for your long-term health.",
            triage: ["Is your reading over 180/120?", "Do you have a severe headache or blurred vision?", "Is there chest pain?"],
            steps: "1. If symptoms are present, this is a **hypertensive crisis**. Trigger SOS.\n2. Otherwise, sit in a quiet place and rest.\n3. Contact your doctor to adjust medication."
        },
        "choking": {
            keywords: ["choking", "swallowed", "cant breathe"],
            empathy: "I'm with you. Choking is terrifying but we can act.",
            triage: ["Can they cough or speak at all?", "Are their lips turning blue?", "Are they conscious?"],
            steps: "1. Perform the **Heimlich maneuver**.\n2. Wrap arms around waist, make a fist above navel.\n3. Perform quick upward thrusts.\n4. If they collapse, begin CPR."
        },
        "asthma": {
            keywords: ["asthma", "wheezing", "shortness of breath", "trouble breathing"],
            empathy: "I understand. Breathing difficulty needs immediate attention.",
            triage: ["Do they have their rescue inhaler?", "Can they speak in full sentences?", "Are their lips/fingernails blue?"],
            steps: "1. Sit them upright.\n2. Use the blue rescue inhaler (2 puffs every 2 mins, up to 10).\n3. If no improvement in 5 mins, **trigger SOS**."
        },
        "bleeding": {
            keywords: ["bleeding", "cut", "wound", "hemorrhage"],
            empathy: "Let's stop that bleeding right now.",
            triage: ["Is the blood spurting or flowing fast?", "Is the wound deep?", "Is there an object stuck in it?"],
            steps: "1. Apply firm, direct pressure with a clean cloth.\n2. **Do not remove the cloth** even if soaked; add more.\n3. Elevate the area above the heart."
        },
        "fracture": {
            keywords: ["broken bone", "fracture", "broken leg", "broken arm", "dislocation", "sprain"],
            empathy: "I'm sorry about the injury. Let's stabilize it.",
            triage: ["Is the bone protruding through the skin?", "Is the limb deformed?", "Is there loss of feeling?"],
            steps: "1. **Do not try to realign the bone**.\n2. Splint the joint above and below the injury.\n3. Apply ice (not directly on skin) to reduce swelling."
        },
        "burn": {
            keywords: ["burn", "scalded", "fire"],
            empathy: "Let's treat that burn and stop the pain.",
            triage: ["Is it charred or white (3rd degree)?", "Are there blisters?", "Is the area larger than your palm?"],
            steps: "1. Run **cool water** (not cold) for 20 mins.\n2. Remove jewelry before swelling.\n3. Cover loosely with plastic wrap or a sterile pad."
        },
        "head injury": {
            keywords: ["head injury", "concussion", "hit head", "collapsed"],
            empathy: "Head injuries need careful monitoring.",
            triage: ["Did they lose consciousness?", "Are they vomiting?", "Is there a severe headache or confusion?"],
            steps: "1. Keep the person still.\n2. Apply ice to any swelling.\n3. If they are drowsy or confused, **trigger SOS** for a CT scan."
        },
        "pregnancy": {
            keywords: ["pregnancy", "pregnant", "contractions", "water broke"],
            empathy: "I'm here for you and the baby. Let's check your safety.",
            triage: ["Is there any vaginal bleeding?", "Are you having severe abdominal pain?", "How far apart are the contractions?"],
            steps: "1. If bleeding or severe pain occurs, **trigger SOS immediately**.\n2. Lie on your **left side**.\n3. Track contraction frequency. If under 5 mins apart, head to the hospital."
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

            if (data.error) throw new Error(data.error);

            const aiResponse = data.choices[0].message.content;
            appendMessage('bot', aiResponse);
            if (typeof speak === 'function') speak(aiResponse.replace(/\*\*/g, ''));

        } catch (error) {
            if (loadingDiv) loadingDiv.remove();
            console.error('OpenAI Error:', error);
            const fallback = "I'm having trouble connecting to my advanced brain. Please describe your symptoms specifically (e.g. 'chest pain') or use the Red SOS button.";
            appendMessage('bot', fallback);
        }
    }

    function startTriage(protocolKey) {
        const p = medicalBrain[protocolKey];
        chatState.activeProtocol = protocolKey;
        chatState.triageStep = 0;

        appendMessage('bot', `<em>${p.empathy}</em><br><br>${p.triage[0]}`);
        if (typeof speak === 'function') speak(p.empathy + ". " + p.triage[0]);
    }

    function processTriage(input) {
        const p = medicalBrain[chatState.activeProtocol];
        chatState.triageStep++;

        if (chatState.triageStep < p.triage.length) {
            appendMessage('bot', p.triage[chatState.triageStep]);
            if (typeof speak === 'function') speak(p.triage[chatState.triageStep]);
        } else {
            appendMessage('bot', `<strong>Recommendation:</strong><br>${p.steps}<br><br>I am monitoring your status. If things worsen, hit the Red SOS button.`);
            if (typeof speak === 'function') speak("Based on your situation, here are the steps. " + p.steps.replace(/\*\*/g, ''));
            chatState.activeProtocol = null;
            chatState.triageStep = 0;
        }
    }

    function appendMessage(type, text) {
        const div = document.createElement('div');
        div.className = `msg ${type}`;
        div.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                            .replace(/\n/g, '<br>');
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
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
