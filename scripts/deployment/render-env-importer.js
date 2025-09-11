#!/usr/bin/env node

/**
 * Render Environment Variables Importer
 * 
 * This script helps you quickly copy environment variables to Render dashboard.
 * It reads the render-config.json file and formats the variables for easy copying.
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

console.log('🚀 Render Environment Variables Importer');
console.log('==========================================\n');

try {
    // Read the configuration file
    const configPath = path.join(__dirname, 'render-config.json');
    const configData = fs.readFileSync(configPath, 'utf8');
    const config = JSON.parse(configData);

    const { envVars, sensitiveVars, instructions } = config.render;

    console.log('📋 INSTRUCTIONS:');
    Object.entries(instructions).forEach(([key, instruction]) => {
        console.log(`   ${key}. ${instruction}`);
    });
    console.log('');

    console.log('🔧 NON-SENSITIVE ENVIRONMENT VARIABLES:');
    console.log('   (Copy these directly to Render dashboard)\n');
    
    Object.entries(envVars).forEach(([key, value]) => {
        console.log(`${key}=${value}`);
    });

    console.log('\n🔐 SENSITIVE VARIABLES (SET MANUALLY):');
    console.log('   (Replace with your actual values)\n');

    console.log('   REQUIRED:');
    sensitiveVars.required.forEach(varName => {
        console.log(`   ${varName}=YOUR_ACTUAL_VALUE_HERE`);
    });

    console.log('\n   OPTIONAL:');
    sensitiveVars.optional.forEach(varName => {
        console.log(`   ${varName}=YOUR_ACTUAL_VALUE_HERE`);
    });

    console.log('\n📝 QUICK COPY FORMAT:');
    console.log('   Use this format in Render dashboard:\n');
    
    // Show first few as examples
    const examples = Object.entries(envVars).slice(0, 3);
    examples.forEach(([key, value]) => {
        console.log(`   Key: ${key}`);
        console.log(`   Value: ${value}`);
        console.log('   ---');
    });

    console.log('\n✅ NEXT STEPS:');
    console.log('   1. Generate APP_KEY: php artisan key:generate --show');
    console.log('   2. Get database credentials from your provider');
    console.log('   3. Go to Render dashboard > Your Service > Environment');
    console.log('   4. Add each variable using "Add Environment Variable" button');
    console.log('   5. Deploy your service');

    console.log('\n🔗 HEALTH CHECK:');
    console.log('   After deployment, check: https://your-app-name.onrender.com/debug/health');

} catch (error) {
    console.error('❌ Error reading configuration:', error.message);
    console.log('\n💡 Make sure render-config.json exists in the same directory.');
}

console.log('\n==========================================');
console.log('🎉 Configuration export completed!');
