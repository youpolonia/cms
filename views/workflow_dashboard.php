<!DOCTYPE html>
<html>
<head>
    <title>Workflow Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .progress-container {
            margin: 20px 0;
        }
        .progress-bar {
            height: 30px;
            background-color: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress {
            height: 100%;
            background-color: #4CAF50;
            width: 0%;
            transition: width 0.3s;
        }
        .phase {
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .phase-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .task {
            padding: 8px;
            margin: 5px 0;
            background: white;
            border-left: 4px solid #ddd;
        }
        .task.completed {
            border-left-color: #4CAF50;
        }
        .task.failed {
            border-left-color: #f44336;
        }
        .status {
            padding: 5px 10px;
            border-radius: 3px;
            color: white;
            font-size: 12px;
        }
        .status-running {
            background: #2196F3;
        }
        .status-completed {
            background: #4CAF50;
        }
        .status-failed {
            background: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Workflow Execution Dashboard</h1>
        
        <div class="progress-container">
            <h3>Overall Progress</h3>
            <div class="progress-bar">
                <div class="progress" id="overall-progress"></div>
            </div>
            <div id="progress-text">0% complete</div>
        </div>

        <div id="phases-container">
            <!-- Phases will be loaded here via AJAX -->
        </div>

        <div id="errors-container" style="display: none;">
            <h3>Errors</h3>
            <div id="errors-list"></div>
        </div>
    </div>

    <script>
        // Refresh data every 5 seconds
        setInterval(updateDashboard, 5000);
        
        function updateDashboard() {
            fetch('/api/workflow/status')
                .then(response => response.json())
                .then(data => {
                    // Update progress
                    document.getElementById('overall-progress').style.width = data.progress + '%';
                    document.getElementById('progress-text').textContent = 
                        Math.round(data.progress) + '% complete';

                    // Load phases via separate API call
                    loadPhases();
                });
        }

        function loadPhases() {
            fetch('/api/workflow/phases')
                .then(response => response.json())
                .then(phases => {
                    const container = document.getElementById('phases-container');
                    container.innerHTML = '';
                    
                    phases.forEach(phase => {
                        const phaseEl = document.createElement('div');
                        phaseEl.className = 'phase';
                        phaseEl.innerHTML = `
                            <div class="phase-header">
                                <h3>Phase ${phase.phase}: ${phase.name}</h3>
                                <span class="status status-${phase.status}">${phase.status}</span>
                            </div>
                            <div id="tasks-${phase.phase}"></div>
                        `;
                        container.appendChild(phaseEl);
                        
                        loadTasks(phase.phase);
                    });
                });
        }

        function loadTasks(phaseId) {
            fetch(`/api/workflow/phase/${phaseId}/tasks`)
                .then(response => response.json())
                .then(tasks => {
                    const container = document.getElementById(`tasks-${phaseId}`);
                    container.innerHTML = '';
                    
                    tasks.forEach(task => {
                        const taskEl = document.createElement('div');
                        taskEl.className = `task ${task.status}`;
                        taskEl.innerHTML = `
                            <strong>${task.task}: ${task.name}</strong>
                            <div>Status: <span class="status status-${task.status}">${task.status}</span></div>
                            ${task.error ? `<div class="error">Error: ${task.error}</div>` : ''}
                        `;
                        container.appendChild(taskEl);
                    });
                });
        }

        // Initial load
        updateDashboard();
?>    </script>
</body>
</html>
