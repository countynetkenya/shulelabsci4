#!/bin/bash

# ShuleLabs Persona Launcher
# Generates the commands to run persona bots in separate terminals

echo "=================================================="
echo "🚀 ShuleLabs Persona Testing Launcher"
echo "=================================================="
echo ""
echo "To start the traffic simulators, open 3 NEW TERMINALS"
echo "and run one of the following commands in each:"
echo ""
echo "--- Terminal 1 (Student Bot) ---"
echo "php scripts/simulate_traffic.php --role=student --interval=3"
echo ""
echo "--- Terminal 2 (Teacher Bot) ---"
echo "php scripts/simulate_traffic.php --role=teacher --interval=4"
echo ""
echo "--- Terminal 3 (Admin Bot) ---"
echo "php scripts/simulate_traffic.php --role=admin --interval=5"
echo ""
echo "=================================================="
echo "💡 Tip: Keep these running while you code."
echo "   If you break something, you'll see RED 🔴 errors instantly."
echo "=================================================="
