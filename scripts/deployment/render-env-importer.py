#!/usr/bin/env python3
"""
Render Environment Variables Importer (Python Version)

This script helps you quickly format environment variables for Render dashboard.
It reads the render-config.json file and provides formatted output.
"""

import json
import os

def main():
    print("🚀 Render Environment Variables Importer (Python)")
    print("=" * 50)
    print()

    try:
        # Read the configuration file
        config_path = os.path.join(os.path.dirname(__file__), 'render-config.json')
        
        with open(config_path, 'r') as f:
            config = json.load(f)

        render_config = config['render']
        env_vars = render_config['envVars']
        sensitive_vars = render_config['sensitiveVars']
        instructions = render_config['instructions']

        print("📋 INSTRUCTIONS:")
        for key, instruction in instructions.items():
            print(f"   {key}. {instruction}")
        print()

        print("🔧 NON-SENSITIVE ENVIRONMENT VARIABLES:")
        print("   (Copy these directly to Render dashboard)")
        print()
        
        for key, value in env_vars.items():
            print(f"{key}={value}")

        print()
        print("🔐 SENSITIVE VARIABLES (SET MANUALLY):")
        print("   (Replace with your actual values)")
        print()

        print("   REQUIRED:")
        for var_name in sensitive_vars['required']:
            print(f"   {var_name}=YOUR_ACTUAL_VALUE_HERE")

        print()
        print("   OPTIONAL:")
        for var_name in sensitive_vars['optional']:
            print(f"   {var_name}=YOUR_ACTUAL_VALUE_HERE")

        print()
        print("📝 RENDER DASHBOARD FORMAT:")
        print("   Add variables one by one in this format:")
        print()
        
        # Show first few as examples
        example_vars = list(env_vars.items())[:3]
        for key, value in example_vars:
            print(f"   Variable Name: {key}")
            print(f"   Variable Value: {value}")
            print("   " + "-" * 30)

        print()
        print("✅ NEXT STEPS:")
        print("   1. Generate APP_KEY: php artisan key:generate --show")
        print("   2. Get database credentials from your provider")
        print("   3. Go to Render dashboard > Your Service > Environment")
        print("   4. Click 'Add Environment Variable' for each variable")
        print("   5. Deploy your service")

        print()
        print("🔗 POST-DEPLOYMENT:")
        print("   Health Check: https://your-app-name.onrender.com/debug/health")
        print("   App Info: https://your-app-name.onrender.com/debug/info")

        print()
        print("💡 TIP: You can also use the render.yaml file for automatic deployment!")

    except FileNotFoundError:
        print("❌ Error: render-config.json not found!")
        print("   Make sure you're running this script from the project directory.")
    except json.JSONDecodeError:
        print("❌ Error: Invalid JSON in render-config.json!")
    except Exception as e:
        print(f"❌ Error: {str(e)}")

    print()
    print("=" * 50)
    print("🎉 Configuration export completed!")

if __name__ == "__main__":
    main()
