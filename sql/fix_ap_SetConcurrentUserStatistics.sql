
USE AionAccounts;
GO

ALTER PROCEDURE dbo.ap_SetConcurrentUserStatistics
    @serverNo SMALLINT,
    @concurrentWorldUserCount INT,
    @concurrentUserLimit INT,
    @concurrentAuthUserCount INT,
    @concurrentAuthWaitCount INT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE @now DATETIME = GETDATE();

    BEGIN TRY
        BEGIN TRANSACTION;

        IF EXISTS (
            SELECT 1
            FROM dbo.user_count WITH (UPDLOCK, HOLDLOCK)
            WHERE server_id = @serverNo
        )
        BEGIN
            UPDATE dbo.user_count
            SET
                world_user = @concurrentWorldUserCount,
                limit_user = @concurrentUserLimit,
                auth_user = @concurrentAuthUserCount,
                wait_user = @concurrentAuthWaitCount,
                record_time = @now,
                dayofweek = DATEPART(WEEKDAY, @now)
            WHERE server_id = @serverNo;
        END
        ELSE
        BEGIN
            INSERT INTO dbo.user_count (
                server_id,
                world_user,
                limit_user,
                auth_user,
                wait_user
            )
            VALUES (
                @serverNo,
                @concurrentWorldUserCount,
                @concurrentUserLimit,
                @concurrentAuthUserCount,
                @concurrentAuthWaitCount
            );
        END;

        COMMIT TRANSACTION;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        THROW;
    END CATCH;
END;
GO
