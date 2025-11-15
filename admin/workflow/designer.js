document.addEventListener('DOMContentLoaded', function() {
    const designArea = document.getElementById('design-area');
    const toolboxItems = document.querySelectorAll('.trigger-item, .action-item');
    const propertiesForm = document.getElementById('properties-form');
    const saveBtn = document.getElementById('save-workflow');
    
    let selectedNode = null;
    let nodeCounter = 0;
    const nodes = [];
    
    // Make toolbox items draggable
    toolboxItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', item.dataset.type);
        });
    });
    
    // Handle drop on design area
    designArea.addEventListener('dragover', function(e) {
        e.preventDefault();
    });
    
    designArea.addEventListener('drop', function(e) {
        e.preventDefault();
        const type = e.dataTransfer.getData('text/plain');
        const x = e.clientX - designArea.getBoundingClientRect().left;
        const y = e.clientY - designArea.getBoundingClientRect().top;
        
        createNode(type, x, y);
    });
    
    // Create a new workflow node
    function createNode(type, x, y) {
        nodeCounter++;
        const nodeId = `node-${nodeCounter}`;
        const node = document.createElement('div');
        node.className = `workflow-node ${type.includes('content') || type.includes('schedule') ? 'trigger' : 'action'}`;
        node.id = nodeId;
        node.style.left = `${x}px`;
        node.style.top = `${y}px`;
        node.dataset.type = type;
        
        const nodeTitle = document.createElement('div');
        nodeTitle.className = 'node-title';
        nodeTitle.textContent = type.replace('-', ' ');
        
        const connector = document.createElement('div');
        connector.className = 'node-connector';
        connector.dataset.nodeId = nodeId;
        
        node.appendChild(nodeTitle);
        node.appendChild(connector);
        designArea.appendChild(node);
        
        // Make node draggable
        node.addEventListener('mousedown', startDrag);
        
        // Select node on click
        node.addEventListener('click', function(e) {
            e.stopPropagation();
            selectNode(node);
        });
        
        nodes.push({
            id: nodeId,
            element: node,
            type: type,
            x: x,
            y: y,
            connections: []
        });
    }
    
    // Node dragging functionality
    function startDrag(e) {
        const node = e.currentTarget;
        const startX = e.clientX;
        const startY = e.clientY;
        const startLeft = parseInt(node.style.left) || 0;
        const startTop = parseInt(node.style.top) || 0;
        
        function moveNode(e) {
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            node.style.left = `${startLeft + dx}px`;
            node.style.top = `${startTop + dy}px`;
        }
        
        function stopDrag() {
            document.removeEventListener('mousemove', moveNode);
            document.removeEventListener('mouseup', stopDrag);
        }
        
        document.addEventListener('mousemove', moveNode);
        document.addEventListener('mouseup', stopDrag);
    }
    
    // Select node and show properties
    function selectNode(node) {
        if (selectedNode) {
            selectedNode.classList.remove('selected');
        }
        
        selectedNode = node;
        node.classList.add('selected');
        
        // Update properties form based on node type
        const type = node.dataset.type;
        let formHtml = '';
        
        if (type.includes('content')) {
            formHtml = `
                <div class="form-group">
                    <label>Content Type:</label>
                    <select name="content-type">
                        <option value="post">Post</option>
                        <option value="page">Page</option>
                    </select>
                </div>
            `;
        } else if (type === 'email') {
            formHtml = `
                <div class="form-group">
                    <label>Recipient:</label>
                    <input type="text" name="email-recipient" required>
                </div>
                <div class="form-group">
                    <label>Subject:</label>
                    <input type="text" name="email-subject" required>
                </div>
            `;
        }
        
        propertiesForm.innerHTML = formHtml;
    }
    
    // Save workflow
    saveBtn.addEventListener('click', function() {
        const workflowData = {
            nodes: nodes.map(node => ({
                id: node.id,
                type: node.type,
                x: parseInt(node.element.style.left) || 0,
                y: parseInt(node.element.style.top) || 0,
                properties: getNodeProperties(node.id)
            })),
            connections: []
        };
        
        console.log('Workflow saved:', workflowData);
        alert('Workflow saved successfully');
    });
    
    // Get properties for a node
    function getNodeProperties(nodeId) {
        const node = nodes.find(n => n.id === nodeId);
        if (!node) return {};
        
        const props = {};
        const inputs = propertiesForm.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            props[input.name] = input.value;
        });
        
        return props;
    }
    
    // Deselect node when clicking on design area
    designArea.addEventListener('click', function() {
        if (selectedNode) {
            selectedNode.classList.remove('selected');
            selectedNode = null;
            propertiesForm.innerHTML = '';
        }
    });
});