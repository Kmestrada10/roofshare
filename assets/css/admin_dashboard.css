/* Base Styles */
:root {
    --primary-color: #007BFF;
    --primary-hover: #0069d9;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --border-radius: 8px;
    --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Montserrat', sans-serif;
    background-color: #f5f7fa;
    color: #333;
    line-height: 1.6;
}

/* Dashboard Layout */
.dashboard-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.dashboard-header {
    background-color: var(--primary-color);
    color: white;
    padding: 1rem 2rem;
    box-shadow: var(--box-shadow);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-name {
    font-weight: 600;
}

.verification-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: capitalize;
}

.verification-status.pending {
    background-color: var(--warning-color);
    color: var(--dark-color);
}

.verification-status.verified {
    background-color: var(--success-color);
    color: white;
}

/* Navigation */
.dashboard-nav {
    display: flex;
    gap: 1rem;
}

.logout-btn {
    color: white;
    text-decoration: none;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.2);
    transition: var(--transition);
}

.logout-btn:hover {
    background-color: rgba(255, 255, 255, 0.3);
}

/* Main Content */
.dashboard-main {
    flex: 1;
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}

.dashboard-section {
    margin-bottom: 2rem;
}

.dashboard-section h2 {
    margin-bottom: 1.5rem;
    color: var(--dark-color);
    font-weight: 600;
}

/* Alert Messages */
.alert-message {
    padding: 1rem;
    border-radius: var(--border-radius);
    margin-bottom: 2rem;
    font-weight: 500;
}

.alert-message.warning {
    background-color: rgba(255, 193, 7, 0.2);
    border-left: 4px solid var(--warning-color);
    color: #856404;
}

/* Properties Grid */
.properties-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.property-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.property-card.placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
    min-height: 200px;
    color: var(--secondary-color);
}

.add-property-btn {
    display: inline-block;
    margin-top: 1rem;
    padding: 0.75rem 1.5rem;
    background-color: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: var(--border-radius);
    font-weight: 500;
    transition: var(--transition);
}

.add-property-btn:hover {
    background-color: var(--primary-hover);
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .dashboard-main {
        padding: 1.5rem;
    }
    
    .properties-grid {
        grid-template-columns: 1fr;
    }
}
