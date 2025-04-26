import { ref } from 'vue';

/**
 * MCP Tool Composable
 * Provides access to MCP server tools
 */
export function useMCPTool(serverName) {
    const error = ref(null);
    const isLoading = ref(false);

    /**
     * Execute an MCP tool
     * @param {string} toolName - Name of the tool to execute
     * @param {object} args - Arguments for the tool
     * @returns {Promise<any>} Tool execution result
     */
    const executeTool = async (toolName, args) => {
        isLoading.value = true;
        error.value = null;
        
        try {
            const response = await window.Cline.executeTool({
                server_name: serverName,
                tool_name: toolName,
                arguments: args
            });
            
            if (response.error) {
                throw new Error(response.error);
            }
            
            return response.result;
        } catch (err) {
            error.value = err.message;
            console.error(`MCP Tool Error (${serverName}.${toolName}):`, err);
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Access an MCP resource
     * @param {string} uri - Resource URI
     * @returns {Promise<any>} Resource data
     */
    const accessResource = async (uri) => {
        isLoading.value = true;
        error.value = null;
        
        try {
            const response = await window.Cline.accessResource({
                server_name: serverName,
                uri: uri
            });
            
            if (response.error) {
                throw new Error(response.error);
            }
            
            return response.result;
        } catch (err) {
            error.value = err.message;
            console.error(`MCP Resource Error (${serverName}):`, err);
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    return {
        // Tool methods
        cache_file: (args) => executeTool('cache_file', args),
        get_cached_file: (args) => executeTool('get_cached_file', args),
        
        // Resource methods
        access_mcp_resource: (args) => accessResource(args.uri),
        
        // Status properties
        error,
        isLoading
    };
}
