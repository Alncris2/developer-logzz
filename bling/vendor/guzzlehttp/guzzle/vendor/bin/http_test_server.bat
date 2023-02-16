@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../php-http/client-integration-tests/bin/http_test_server
bash "%BIN_TARGET%" %*
