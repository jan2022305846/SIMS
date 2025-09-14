#!/bin/bash

echo "🚀 Starting USTP Supply Office Development Environment"
echo "======================================================"

# Check if Laravel server is already running
if pgrep -f "php artisan serve" > /dev/null; then
    echo "✅ Laravel server already running"
else
    echo "🌟 Starting Laravel server..."
    php artisan serve &
    sleep 2
fi

# Check if Vite dev server is already running
if pgrep -f "vite" > /dev/null; then
    echo "✅ Vite dev server already running"
else
    echo "🎨 Starting Vite development server..."
    npm run dev &
    sleep 2
fi

echo ""
echo "🎉 Development environment ready!"
echo "📱 Laravel App: http://127.0.0.1:8000"
echo "⚡ Vite Dev Server: http://localhost:5173"
echo ""
echo "💡 Tips:"
echo "   - Edit styles in: resources/sass/app.scss"
echo "   - Edit JS in: resources/js/app.js"
echo "   - Changes auto-reload in browser"
echo "   - Use Ctrl+C to stop this script"
echo ""

# Keep script running
wait
