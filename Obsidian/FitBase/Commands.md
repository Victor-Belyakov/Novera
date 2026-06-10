**Export db:** docker exec -i demo mysql -u root -proot demo < ~/Документы/Dump/demo.sql
**Migrations:** php yii db-migrate 
**Tests:** php vendor/bin/codecept run (все тесты) , php ../vendor/bin/codecept run unit tests/unit/validators/ScheduleRemainVisitsValidatorTest.php (из папки api)
**Worker:** php yii daemon/cron-schedule-auto-transactions-dispatcher verbose
**Migrations-test:** php yii_test migrate 
**Commit revert:** git reset --soft HEAD~1
**TestsAutomatic:** php vendor/bin/codecept run common/tests/unit/automatic
**Worker**: php yii queue-reports/listen 1 
