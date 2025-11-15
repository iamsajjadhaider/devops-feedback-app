-- Create the table if it does not exist
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feedback_text TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'new', -- e.g., 'new', 'in-progress', 'done'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some initial dummy data for testing the Admin/Filtration features
INSERT INTO feedback (feedback_text, status) VALUES
('The site is slow when submitting a form.', 'new'),
('I love the new design!', 'done'),
('The admin filter is not working for me.', 'in-progress'),
('Feature request: dark mode!', 'new'),
('Found a minor spelling error on the landing page.', 'new');
