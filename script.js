document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission
    const ticketForm = document.getElementById('ticket-form');
    if (ticketForm) {
        ticketForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const statusMessage = document.getElementById('status-message');
            
            fetch('process_ticket.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusMessage.className = 'success';
                    statusMessage.textContent = 'Ticket submitted successfully! Ticket ID: ' + data.ticketId;
                    ticketForm.reset();
                } else {
                    statusMessage.className = 'error';
                    statusMessage.textContent = 'Error: ' + data.message;
                }
                statusMessage.style.display = 'block';
                
                // Hide message after 5 seconds
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                }, 5000);
            })
            .catch(error => {
                statusMessage.className = 'error';
                statusMessage.textContent = 'Network error: ' + error;
                statusMessage.style.display = 'block';
                
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                }, 5000);
            });
        });
    }
    
    // Tab switching functionality
    const logTicketTab = document.getElementById('log-ticket-tab');
    const viewTicketsTab = document.getElementById('view-tickets-tab');
    const logTicketSection = document.getElementById('log-ticket-section');
    
    if (logTicketTab && viewTicketsTab && logTicketSection) {
        logTicketTab.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.add('active');
            viewTicketsTab.classList.remove('active');
            logTicketSection.classList.add('active-section');
        });
        
        viewTicketsTab.addEventListener('click', function(e) {
            this.classList.add('active');
            logTicketTab.classList.remove('active');
            logTicketSection.classList.remove('active-section');
            // The tickets.php page will handle its own display
        });
    }
});