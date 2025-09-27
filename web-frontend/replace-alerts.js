// Batch Alert Replacement Script
// This script helps replace all alert() calls with toast notifications

const fs = require('fs');
const path = require('path');

// Define the replacements
const replacements = [
  // Success alerts
  { 
    search: /alert\('([^']*successfully[^']*!)'\)/gi, 
    replace: "toast.success('$1')" 
  },
  { 
    search: /alert\('([^']*success[^']*)'\)/gi, 
    replace: "toast.success('$1')" 
  },
  { 
    search: /alert\('([^']*completed[^']*)'\)/gi, 
    replace: "toast.success('$1')" 
  },
  { 
    search: /alert\('([^']*created[^']*)'\)/gi, 
    replace: "toast.success('$1')" 
  },
  { 
    search: /alert\('([^']*updated[^']*)'\)/gi, 
    replace: "toast.success('$1')" 
  },
  { 
    search: /alert\('([^']*deleted[^']*)'\)/gi, 
    replace: "toast.success('$1')" 
  },
  { 
    search: /alert\('([^']*saved[^']*)'\)/gi, 
    replace: "toast.success('$1')" 
  },
  
  // Error alerts
  { 
    search: /alert\('([^']*failed[^']*)'\)/gi, 
    replace: "toast.error('$1')" 
  },
  { 
    search: /alert\('([^']*error[^']*)'\)/gi, 
    replace: "toast.error('$1')" 
  },
  { 
    search: /alert\(error\.response\?\.[^)]*\)/gi, 
    replace: "toast.error(error.response?.data?.message || 'An error occurred')" 
  },
  
  // Warning alerts
  { 
    search: /alert\('([^']*please[^']*)'\)/gi, 
    replace: "toast.warning('$1')" 
  },
  { 
    search: /alert\('([^']*select[^']*)'\)/gi, 
    replace: "toast.warning('$1')" 
  },
  
  // Generic alerts
  { 
    search: /alert\('([^']*)'\)/gi, 
    replace: "toast.info('$1')" 
  }
];

// List of files to process
const filesToProcess = [
  './src/views/Cars.vue',
  './src/views/Invoices.vue',
  './src/views/CarExpensesTracking.vue',
  './src/views/GovernoratesWilayats.vue',
  './src/views/WilayatBranchAssignments.vue',
  './src/views/PartnerServiceRequest.vue',
  './src/components/WorkflowRoleManagement.vue'
];

function addToastImport(content) {
  // Check if toast import already exists
  if (content.includes("from '../composables/useToast'") || content.includes("import { toast }")) {
    return content;
  }
  
  // Find the import section and add toast import
  const vueImportMatch = content.match(/import \{[^}]*\} from 'vue'/);
  if (vueImportMatch) {
    const insertIndex = content.indexOf(vueImportMatch[0]) + vueImportMatch[0].length;
    return content.slice(0, insertIndex) + "\nimport { toast } from '../composables/useToast'" + content.slice(insertIndex);
  }
  
  // If no Vue import found, add at the top of script section
  const scriptMatch = content.match(/<script[^>]*>/);
  if (scriptMatch) {
    const insertIndex = content.indexOf(scriptMatch[0]) + scriptMatch[0].length;
    return content.slice(0, insertIndex) + "\nimport { toast } from '../composables/useToast'" + content.slice(insertIndex);
  }
  
  return content;
}

function processFile(filePath) {
  try {
    console.log(`Processing: ${filePath}`);
    
    if (!fs.existsSync(filePath)) {
      console.log(`File not found: ${filePath}`);
      return;
    }
    
    let content = fs.readFileSync(filePath, 'utf8');
    let alertCount = 0;
    
    // Count existing alerts
    const alertMatches = content.match(/alert\(/g);
    if (alertMatches) {
      alertCount = alertMatches.length;
      console.log(`Found ${alertCount} alert() calls`);
    } else {
      console.log('No alerts found');
      return;
    }
    
    // Add toast import if needed
    content = addToastImport(content);
    
    // Apply replacements
    let replacementCount = 0;
    replacements.forEach(({ search, replace }) => {
      const before = content;
      content = content.replace(search, replace);
      if (content !== before) {
        replacementCount++;
      }
    });
    
    // Handle confirm() statements too
    content = content.replace(
      /if \(confirm\(`([^`]*)`\)\) \{\s*([^}]*)\s*\}/g,
      `toast.confirm('$1', { onConfirm: () => { $2 } })`
    );
    
    content = content.replace(
      /if \(confirm\('([^']*)'\)\) \{\s*([^}]*)\s*\}/g,
      `toast.confirm('$1', { onConfirm: () => { $2 } })`
    );
    
    // Write back to file
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`✅ Replaced ${replacementCount} patterns in ${filePath}`);
    
  } catch (error) {
    console.error(`❌ Error processing ${filePath}:`, error.message);
  }
}

// Process all files
console.log('🍞 Alert to Toast Replacement Script\n');
filesToProcess.forEach(processFile);
console.log('\n✅ Batch replacement complete!');