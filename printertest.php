<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonprinter Configuratie - Happy Herbivore</title>
    <link rel="stylesheet" href="assets/menu.css">
    <style>
        .printer-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .printer-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #ff7520;
        }

        .printer-header h1 {
            font-size: 2.2rem;
            color: #053631;
            margin-bottom: 10px;
        }

        .printer-header p {
            color: #666;
            font-size: 1rem;
        }

        .status-box {
            background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 2px solid #f0f0f0;
            text-align: center;
        }

        .status-box.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-color: #8cd003;
        }

        .status-box.error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-color: #ff4d4f;
        }

        .status-box.loading {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border-color: #17a2b8;
        }

        .status-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .status-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #053631;
        }

        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .printer-btn {
            padding: 18px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .printer-btn.primary {
            background: linear-gradient(135deg, #8cd003 0%, #7ab200 100%);
            color: white;
            box-shadow: 0 6px 16px rgba(140, 208, 3, 0.35);
        }

        .printer-btn.primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(140, 208, 3, 0.45);
        }

        .printer-btn.secondary {
            background: linear-gradient(135deg, #ff7520 0%, #ff9147 100%);
            color: white;
            box-shadow: 0 6px 16px rgba(255, 117, 32, 0.35);
        }

        .printer-btn.secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 117, 32, 0.45);
        }

        .printer-btn.tertiary {
            background: linear-gradient(145deg, #f5f5f5 0%, #e8e8e8 100%);
            color: #053631;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .printer-btn.tertiary:hover {
            background: linear-gradient(145deg, #e8e8e8 0%, #dcdcdc 100%);
            transform: translateY(-2px);
        }

        .printer-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .receipt-preview {
            background: #f9f9f9;
            border: 2px dashed #ccc;
            padding: 20px;
            border-radius: 12px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin-bottom: 20px;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 400px;
            overflow-y: auto;
        }

        .info-section {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .info-section h3 {
            color: #053631;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .info-section ul {
            margin-left: 20px;
            color: #0c3d73;
        }

        .info-section li {
            margin-bottom: 5px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #053631 0%, #0a5a4f 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(5, 54, 49, 0.35);
        }
    </style>
</head>
<body class="menu-page">
    <main class="menu-main">
        <div class="printer-container">
            <div class="printer-header">
                <h1>🖨️ Bonprinter Configuratie</h1>
                <p>Test en configureer de USB bonprinter</p>
            </div>

            <div class="status-box" id="statusBox">
                <div class="status-icon">⏳</div>
                <div class="status-text" id="statusText">Klaar om te testen...</div>
            </div>

            <div class="button-grid">
                <button class="printer-btn primary" onclick="testPrint()">
                    <span>🖨️</span> Test Print
                </button>
                <button class="printer-btn secondary" onclick="selectPrinter()">
                    <span>🔍</span> Selecteer Printer
                </button>
                <button class="printer-btn tertiary" onclick="detectPrinter()">
                    <span>🔄</span> Auto-Detect
                </button>
            </div>

            <div class="info-section">
                <h3>📋 Hoe te gebruiken:</h3>
                <ul>
                    <li><strong>Auto-Detect:</strong> Zoekt automatisch naar een aangesloten printer</li>
                    <li><strong>Selecteer Printer:</strong> Kies handmatig welke printer je wilt gebruiken</li>
                    <li><strong>Test Print:</strong> Print een testbon om de verbinding te testen</li>
                </ul>
            </div>

            <div class="info-section" style="background: #fff3cd; border-color: #ff7520;">
                <h3>⚠️ Belangrijke informatie:</h3>
                <ul>
                    <li>Gebruik <strong>Google Chrome</strong> of <strong>Microsoft Edge</strong> (WebUSB ondersteuning vereist)</li>
                    <li>Sluit de printer aan via USB voordat je begint</li>
                    <li>Bij de eerste keer moet je de printer handmatig selecteren</li>
                    <li>De printer wordt automatisch onthouden voor volgende keren</li>
                </ul>
            </div>

            <div class="receipt-preview" id="receiptPreview">
====================================
     HAPPY HERBIVORE
     Vegan Restaurant
     Amsterdam
====================================

Bestelnummer: TEST-001

<?php echo date('d-m-Y H:i:s'); ?>

------------------------------------
1x Test Burger           €10.00
1x Friet                  €3.50
1x Cola                   €2.50
------------------------------------
Subtotaal:               €16.00
Bezorgkosten:             €0.00
------------------------------------
TOTAAL:                  €16.00

Betaald met: Pinpas

Bedankt voor uw bezoek!
Tot ziens!
====================================
            </div>

            <a href="index.php" class="back-link">← Terug naar Home</a>
        </div>
    </main>

    <script src="assets/printer.js"></script>
    <script>
        function updateStatus(icon, text, type = 'info') {
            const statusBox = document.getElementById('statusBox');
            const statusIcon = statusBox.querySelector('.status-icon');
            const statusText = document.getElementById('statusText');

            statusIcon.textContent = icon;
            statusText.textContent = text;

            statusBox.className = 'status-box';
            if (type === 'success') statusBox.classList.add('success');
            if (type === 'error') statusBox.classList.add('error');
            if (type === 'loading') statusBox.classList.add('loading');
        }

        async function detectPrinter() {
            updateStatus('🔄', 'Zoeken naar printer...', 'loading');
            
            const detected = await window.receiptPrinter.autoDetect();
            
            if (detected) {
                updateStatus('✅', 'Printer gevonden en geselecteerd!', 'success');
            } else {
                updateStatus('❌', 'Geen printer gevonden. Gebruik "Selecteer Printer"', 'error');
            }
        }

        async function selectPrinter() {
            updateStatus('🔍', 'Selecteer uw printer...', 'loading');
            
            const selected = await window.receiptPrinter.selectPrinter();
            
            if (selected) {
                updateStatus('✅', 'Printer succesvol geselecteerd!', 'success');
            } else {
                updateStatus('❌', 'Geen printer geselecteerd', 'error');
            }
        }

        async function testPrint() {
            updateStatus('🖨️', 'Test bon wordt geprint...', 'loading');
            
            const result = await window.receiptPrinter.testPrint();
            
            if (result.success) {
                updateStatus('✅', result.message, 'success');
            } else {
                updateStatus('❌', 'Print fout: ' + result.message, 'error');
                
                // Offer to select printer
                if (confirm('Wilt u de printer selecteren?')) {
                    await selectPrinter();
                }
            }
        }

        // Check support on load
        window.addEventListener('load', () => {
            if (!window.receiptPrinter.isSupported()) {
                updateStatus('⚠️', 'WebUSB niet ondersteund in deze browser. Gebruik Chrome of Edge.', 'error');
            } else {
                // Auto-detect printer
                detectPrinter();
            }
        });
    </script>
</body>
</html>