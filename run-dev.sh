#!/bin/bash

echo "loading..."
symfony server:start -d

echo "testing..."
php bin/phpunit

echo "✅ ONLINE http://localhost:8000 avec tests exécutés."
