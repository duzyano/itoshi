// printer.js - USB Receipt Printer Integration
class ReceiptPrinter {
    constructor() {
        this.selectedDevice = null;
        this.PRINTER_VENDORS = [
            0x0483, // STM Microelectronics (Xprinter)
            0x04b8, // Seiko Epson
            0x0456, // Microtek
            0x067b, // Prolific Technology
        ];
    }

    // Check if WebUSB is supported
    isSupported() {
        return 'usb' in navigator;
    }

    // Auto-detect printer
    async autoDetect() {
        try {
            if (!this.isSupported()) {
                throw new Error('WebUSB niet ondersteund in deze browser');
            }

            // Try to get already authorized devices
            const devices = await navigator.usb.getDevices();
            const printer = devices.find(device => 
                this.PRINTER_VENDORS.includes(device.vendorId)
            );

            if (printer) {
                this.selectedDevice = printer;
                console.log('Printer gevonden:', printer.productName || printer.manufacturerName);
                return true;
            }

            return false;
        } catch (error) {
            console.error('Auto-detect fout:', error);
            return false;
        }
    }

    // Request user to select printer
    async selectPrinter() {
        try {
            if (!this.isSupported()) {
                throw new Error('WebUSB niet ondersteund');
            }

            const filters = this.PRINTER_VENDORS.map(vendorId => ({ vendorId }));
            this.selectedDevice = await navigator.usb.requestDevice({ filters });
            console.log('Printer geselecteerd:', this.selectedDevice.productName);
            return true;
        } catch (error) {
            if (error.name !== 'NotFoundError') {
                console.error('Selectie fout:', error);
            }
            return false;
        }
    }

    // Build receipt content with ESC/POS commands
    buildReceipt(orderData) {
        const ESC = '\x1B';
        const GS = '\x1D';
        
        // ESC/POS Commands
        const INIT = ESC + '\x40';           // Initialize
        const CENTER = ESC + '\x61\x01';     // Center align
        const LEFT = ESC + '\x61\x00';       // Left align
        const BOLD_ON = ESC + '\x45\x01';    // Bold on
        const BOLD_OFF = ESC + '\x45\x00';   // Bold off
        const DOUBLE_SIZE = ESC + '\x21\x30'; // Double size
        const NORMAL_SIZE = ESC + '\x21\x00'; // Normal size
        const CUT = GS + '\x56\x00';         // Cut paper

        let receipt = INIT;
        receipt += '\n';
        
        // Header
        receipt += CENTER;
        receipt += DOUBLE_SIZE;
        receipt += BOLD_ON;
        receipt += 'HAPPY HERBIVORE\n';
        receipt += BOLD_OFF;
        receipt += NORMAL_SIZE;
        receipt += 'Vegan Restaurant\n';
        receipt += 'Amsterdam\n';
        receipt += '\n';
        
        // Order Number
        receipt += BOLD_ON;
        receipt += 'Bestelnummer: ' + (orderData.orderNumber || '0000') + '\n';
        receipt += BOLD_OFF;
        receipt += '\n';
        
        // Date & Time
        receipt += LEFT;
        const now = new Date();
        receipt += now.toLocaleDateString('nl-NL') + ' ' + now.toLocaleTimeString('nl-NL') + '\n';
        receipt += '------------------------------------\n';
        
        // Items
        if (orderData.items && orderData.items.length > 0) {
            orderData.items.forEach(item => {
                const itemName = this.truncate(item.name, 20);
                const price = '€' + parseFloat(item.price).toFixed(2);
                const spaces = ' '.repeat(Math.max(1, 34 - itemName.length - price.length));
                receipt += '1x ' + itemName + spaces + price + '\n';
            });
        }
        
        receipt += '------------------------------------\n';
        
        // Totals
        if (orderData.subtotal) {
            const subtotal = '€' + parseFloat(orderData.subtotal).toFixed(2);
            receipt += this.formatLine('Subtotaal:', subtotal, 34);
        }
        
        if (orderData.delivery) {
            const delivery = '€' + parseFloat(orderData.delivery).toFixed(2);
            receipt += this.formatLine('Bezorgkosten:', delivery, 34);
        }
        
        receipt += '------------------------------------\n';
        
        if (orderData.total) {
            const total = '€' + parseFloat(orderData.total).toFixed(2);
            receipt += BOLD_ON;
            receipt += this.formatLine('TOTAAL:', total, 34);
            receipt += BOLD_OFF;
        }
        
        receipt += '\n';
        receipt += CENTER;
        receipt += 'Betaald met: Pinpas\n';
        receipt += '\n\n';
        receipt += 'Bedankt voor uw bezoek!\n';
        receipt += 'Tot ziens!\n';
        receipt += '\n\n\n\n';
        
        // Cut paper
        receipt += CUT;
        
        return receipt;
    }

    // Helper: Format line with left and right aligned text
    formatLine(left, right, width = 34) {
        const spaces = ' '.repeat(Math.max(1, width - left.length - right.length));
        return left + spaces + right + '\n';
    }

    // Helper: Truncate text to fit
    truncate(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength - 3) + '...';
    }

    // Print receipt
    async print(orderData) {
        try {
            // If no device selected, try auto-detect or request
            if (!this.selectedDevice) {
                const detected = await this.autoDetect();
                if (!detected) {
                    const selected = await this.selectPrinter();
                    if (!selected) {
                        throw new Error('Geen printer geselecteerd');
                    }
                }
            }

            // Open device
            await this.selectedDevice.open();

            // Select configuration
            if (this.selectedDevice.configuration === null) {
                await this.selectedDevice.selectConfiguration(1);
            }

            // Claim interface
            try {
                await this.selectedDevice.claimInterface(0);
            } catch (e) {
                console.log('Interface al geclaimd, doorgaan...');
            }

            // Build and send receipt
            const receipt = this.buildReceipt(orderData);
            const encoder = new TextEncoder();

            // Find output endpoint
            const intf = this.selectedDevice.configuration.interfaces[0].alternates[0];
            const endpoint = intf.endpoints.find(e => e.direction === 'out');

            if (!endpoint) {
                throw new Error('Output endpoint niet gevonden');
            }

            // Send to printer
            await this.selectedDevice.transferOut(endpoint.endpointNumber, encoder.encode(receipt));

            // Close after delay
            setTimeout(() => {
                this.selectedDevice.close();
            }, 1000);

            return { success: true, message: 'Bon succesvol geprint!' };

        } catch (error) {
            console.error('Print Error:', error);
            return { success: false, message: error.message };
        }
    }

    // Test print
    async testPrint() {
        const testData = {
            orderNumber: 'TEST',
            items: [
                { name: 'Test Burger', price: 10.00 },
                { name: 'Test Friet', price: 3.50 }
            ],
            subtotal: 13.50,
            delivery: 0.00,
            total: 13.50
        };

        return await this.print(testData);
    }
}

// Global printer instance
window.receiptPrinter = new ReceiptPrinter();

// Auto-detect on page load
window.addEventListener('load', () => {
    if (window.receiptPrinter.isSupported()) {
        window.receiptPrinter.autoDetect();
    }
});