<div class="dash-card" style="margin-top: 24px;">
    <h3><i data-lucide="landmark"></i> Bank Account &amp; Withdrawals</h3>

    @if(Auth::user()->paystack_recipient_code)
    <div style="margin-top:16px; padding:16px; background: rgba(255,255,255,0.03); border-radius:12px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
        <div>
            <div style="font-weight:700;">{{ Auth::user()->account_name }}</div>
            <div style="font-size:0.8rem; color:var(--grey);">{{ Auth::user()->bank_name }} &middot; ****{{ substr(Auth::user()->account_number, -4) }}</div>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="button" onclick="openWithdrawModal()" class="btn-primary" style="padding:10px 18px; font-size:0.82rem;">Withdraw</button>
            <button type="button" onclick="openBankModal()" style="background:rgba(255,255,255,0.05); color:var(--white); border:1px solid var(--glass-border); padding:10px 18px; border-radius:8px; font-size:0.82rem; cursor:pointer;">Change</button>
        </div>
    </div>
    @else
    <p style="color:var(--grey); font-size:0.85rem; margin:12px 0;">Add a bank account to withdraw your earnings.</p>
    <button type="button" onclick="openBankModal()" class="btn-primary" style="padding:10px 18px; font-size:0.82rem;">Add Bank Account</button>
    @endif
</div>

<!-- BANK ACCOUNT MODAL -->
<div id="bankModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--dark); border:1px solid var(--glass-border); border-radius:20px; padding:32px; width:100%; max-width:400px; margin:20px; color:var(--white);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0;">Bank Account</h3>
            <button onclick="document.getElementById('bankModal').style.display='none'" style="background:none;border:none;color:var(--grey);cursor:pointer;font-size:1.5rem;">&times;</button>
        </div>
        <div style="margin-bottom:14px;">
            <label style="font-size:0.75rem; color:var(--grey); text-transform:uppercase; display:block; margin-bottom:6px;">Bank</label>
            <select id="bankSelect" style="width:100%; background: rgba(255,255,255,0.05); border:1px solid var(--glass-border); border-radius:8px; padding:10px; color:var(--white); font-size:0.85rem;">
                <option value="">Select your bank…</option>
            </select>
        </div>
        <div style="margin-bottom:14px;">
            <label style="font-size:0.75rem; color:var(--grey); text-transform:uppercase; display:block; margin-bottom:6px;">Account Number</label>
            <input type="text" id="bankAccountNumber" maxlength="10" placeholder="0123456789" style="width:100%; background: rgba(255,255,255,0.05); border:1px solid var(--glass-border); border-radius:8px; padding:10px; color:var(--white); font-size:0.85rem;">
        </div>
        <p id="bankVerifyResult" style="font-size:0.8rem; margin-bottom:14px; min-height:1.2em;"></p>
        <button type="button" onclick="saveBankAccount()" class="btn-primary" style="width:100%; padding:12px;">Verify &amp; Save</button>
    </div>
</div>

<!-- WITHDRAW MODAL -->
<div id="withdrawModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--dark); border:1px solid var(--glass-border); border-radius:20px; padding:32px; width:100%; max-width:380px; margin:20px; color:var(--white);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0;">Withdraw Funds</h3>
            <button onclick="document.getElementById('withdrawModal').style.display='none'" style="background:none;border:none;color:var(--grey);cursor:pointer;font-size:1.5rem;">&times;</button>
        </div>
        <p style="color:var(--grey); font-size:0.85rem; margin-bottom:16px;">Available: ₦{{ number_format(Auth::user()->wallet_balance, 2) }}</p>
        <input type="number" id="withdrawAmount" min="100" placeholder="Amount (₦)" style="width:100%; background: rgba(255,255,255,0.05); border:1px solid var(--glass-border); border-radius:8px; padding:12px; color:var(--white); font-size:0.9rem; margin-bottom:16px;">
        <button type="button" onclick="submitWithdrawal()" class="btn-primary" style="width:100%; padding:12px;">Request Withdrawal</button>
    </div>
</div>

<script>
    let banksLoaded = false;

    function openBankModal() {
        document.getElementById('bankModal').style.display = 'flex';
        if (!banksLoaded) {
            fetch('{{ route('wallet.banks') }}')
                .then(res => res.json())
                .then(banks => {
                    const select = document.getElementById('bankSelect');
                    select.innerHTML = '<option value="">Select your bank…</option>' +
                        banks.map(b => `<option value="${b.code}" data-name="${b.name}">${b.name}</option>`).join('');
                    banksLoaded = true;
                })
                .catch(() => {
                    document.getElementById('bankSelect').innerHTML = '<option value="">Could not load banks</option>';
                });
        }
    }

    function saveBankAccount() {
        const bankSelect = document.getElementById('bankSelect');
        const bankCode = bankSelect.value;
        const bankName = bankSelect.selectedOptions[0] ? bankSelect.selectedOptions[0].dataset.name : '';
        const accountNumber = document.getElementById('bankAccountNumber').value.trim();
        const resultEl = document.getElementById('bankVerifyResult');

        if (!bankCode || accountNumber.length !== 10) {
            resultEl.style.color = 'var(--red)';
            resultEl.textContent = 'Select a bank and enter a valid 10-digit account number.';
            return;
        }

        resultEl.style.color = 'var(--grey)';
        resultEl.textContent = 'Verifying…';

        fetch('{{ route('wallet.bank-account') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ bank_code: bankCode, bank_name: bankName, account_number: accountNumber })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resultEl.style.color = '#22c55e';
                resultEl.textContent = `Verified: ${data.account_name}`;
                setTimeout(() => location.reload(), 1200);
            } else {
                resultEl.style.color = 'var(--red)';
                resultEl.textContent = data.message || 'Could not verify this account.';
            }
        })
        .catch(() => {
            resultEl.style.color = 'var(--red)';
            resultEl.textContent = 'Network error. Please try again.';
        });
    }

    function openWithdrawModal() {
        document.getElementById('withdrawModal').style.display = 'flex';
    }

    function submitWithdrawal() {
        const amount = parseFloat(document.getElementById('withdrawAmount').value);
        if (!amount || amount < 100) {
            alert('Enter a valid amount (minimum ₦100).');
            return;
        }
        fetch('{{ route('wallet.withdraw') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ amount })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Withdrawal initiated. Funds will arrive in your bank account shortly.');
                location.reload();
            } else {
                alert(data.message || 'Could not process withdrawal.');
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    }
</script>
