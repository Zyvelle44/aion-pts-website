
USE [AionAccounts];
GO

CREATE OR ALTER PROCEDURE [dbo].[ap_AutoChangePwd]
    @account VARCHAR(14),
    @pwd NVARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @pwdcr VARBINARY(MAX);

    SELECT @pwdcr =
        CAST(CONVERT(VARBINARY(16), @pwd, 1) AS VARBINARY(MAX));

    IF EXISTS (
        SELECT 1
        FROM dbo.user_auth
        WHERE account = @account
    )
    BEGIN
        UPDATE dbo.user_auth
        SET password = @pwdcr
        WHERE account = @account;

        UPDATE dbo.web_account
        SET
            passwords = @pwdcr,
            password2 = @pwd
        WHERE account = @account;
    END
END;
GO
