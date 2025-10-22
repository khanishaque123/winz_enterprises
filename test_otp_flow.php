<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>OTP Flow Test</title>
    <style>
        .container { max-width: 500px; margin: 50px auto; padding: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        button { padding: 10px 20px; margin: 10px 5px; }
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Test OTP Flow</h2>
        
        <div class="form-group">
            <button onclick="testLoginOTP()">Test Login OTP</button>
            <button onclick="testSignupOTP()">Test Signup OTP</button>
        </div>
        
        <div id="result"></div>
        
        <h3>Or Test Manually:</h3>
        <form onsubmit="return sendOTP(this)">
            <div class="form-group">
                <label>Type:</label>
                <select name="type" required>
                    <option value="login">Login</option>
                    <option value="signup">Signup</option>
                </select>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="test@example.com" required>
            </div>
            <div class="form-group" id="signupFields" style="display:none;">
                <label>Name:</label>
                <input type="text" name="name" value="Test User">
                <label>Mobile:</label>
                <input type="text" name="mobile" value="1234567890">
                <label>Address:</label>
                <textarea name="address">Test Address</textarea>
            </div>
            <button type="submit">Send OTP</button>
        </form>
    </div>

    <script>
        // Show/hide signup fields
        document.querySelector('select[name="type"]').addEventListener('change', function() {
            document.getElementById('signupFields').style.display = 
                this.value === 'signup' ? 'block' : 'none';
        });

        function showResult(message, isSuccess) {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = `<div class="result ${isSuccess ? 'success' : 'error'}">${message}</div>`;
        }

        function testLoginOTP() {
            const payload = {
                email: 'test@example.com',
                type: 'login'
            };
            
            sendOTPRequest(payload);
        }

        function testSignupOTP() {
            const payload = {
                email: 'test@example.com',
                type: 'signup',
                name: 'Test User',
                mobile: '1234567890',
                address: 'Test Address'
            };
            
            sendOTPRequest(payload);
        }

        function sendOTP(form) {
            const formData = new FormData(form);
            const payload = {
                email: formData.get('email'),
                type: formData.get('type')
            };
            
            if (payload.type === 'signup') {
                payload.name = formData.get('name');
                payload.mobile = formData.get('mobile');
                payload.address = formData.get('address');
            }
            
            sendOTPRequest(payload);
            return false;
        }

        function sendOTPRequest(payload) {
            showResult('Sending OTP...', true);
            
            fetch('includes/send_otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = `✅ ${data.message}`;
                    if (data.debug_otp) {
                        message += `<br><strong>DEBUG OTP: ${data.debug_otp}</strong>`;
                    }
                    showResult(message, true);
                    console.log('OTP Response:', data);
                } else {
                    showResult(`❌ ${data.message}`, false);
                    console.error('OTP Error:', data);
                }
            })
            .catch(error => {
                showResult(`❌ Network error: ${error}`, false);
                console.error('Network error:', error);
            });
        }
    </script>
</body>
</html>