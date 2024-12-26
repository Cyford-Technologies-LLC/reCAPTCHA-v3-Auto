<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// And google details
$secret = 'sxtkxtktk26d6HHXHxt2h667sxs20x62';
$site_key = 'dxshtxdthnnxfbn6x64gnxgnYJDazrfgxbh';

function verifyRecaptcha($secret, $response) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secret,
        'response' => $response
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['token'])) {
        $verification = verifyRecaptcha($secret, $data['token']);
        header('Content-Type: application/json');
        echo json_encode($verification);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>reCAPTCHA v3 Auto</title>
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($site_key); ?>"></script>
</head>
<body>
<div id="status">Verifying...</div>

<script>
    // Function to handle the verification process
    function verifyUser() {
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo htmlspecialchars($site_key); ?>', {
                action: 'verify'
            }).then(function(token) {
                // Debug token
                console.log('reCAPTCHA token:', token);

                // Send token to server
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        token: token
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Verification result:', data);

                        if (data.success) {
                            if (data.score > 0.5) {
                                document.getElementById('status').innerHTML = 'Verification successful! Score: ' + data.score;
                                // Add your success logic here
                                console.log('Success - proceed with action');
                            } else {
                                document.getElementById('status').innerHTML = 'Suspicious activity detected. Score: ' + data.score;
                                // Add your failure logic here
                                console.log('Failed - score too low');
                            }
                        } else {
                            document.getElementById('status').innerHTML = 'Verification failed';
                            console.log('Verification failed:', data['error-codes']);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('status').innerHTML = 'Error during verification';
                    });
            });
        });
    }

    // Execute verification immediately when page loads
    document.addEventListener('DOMContentLoaded', function() {
        verifyUser();
    });

    // Optional: Refresh verification every 2 minutes
    setInterval(verifyUser, 120000);
</script>
</body>
</html>
