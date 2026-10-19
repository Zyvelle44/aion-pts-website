
USE [AionAccounts];
GO

CREATE OR ALTER PROCEDURE [dbo].[ap_AutoReg]
    @account VARCHAR(14),
    @otpflag TINYINT,
    @pwd NVARCHAR(50),
    @answer1 NVARCHAR(50),
    @answer2 NVARCHAR(50),
    @pwdFlag TINYINT,
    @email VARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @ssn CHAR(13);
    DECLARE @uid INT;

    DECLARE @pwdcr VARBINARY(MAX);
    DECLARE @answer1cr VARBINARY(MAX);
    DECLARE @answer2cr VARBINARY(MAX);

    SELECT @pwdcr =
        CAST(CONVERT(VARBINARY(16), @pwd, 1) AS VARBINARY(MAX));

    SELECT @answer1cr =
        CAST(CONVERT(VARBINARY(16), @answer1, 1) AS VARBINARY(MAX));

    SELECT @answer2cr =
        CAST(CONVERT(VARBINARY(16), @answer2, 1) AS VARBINARY(MAX));

    IF NOT EXISTS (
        SELECT 1
        FROM dbo.user_auth
        WHERE account = @account
    )
    BEGIN
        SET @ssn =
            SUBSTRING(STR(FLOOR(RAND() * 8999999 + 1000000)), 4, 7)
            + SUBSTRING(STR(FLOOR(RAND() * 899999 + 100000)), 5, 6);

        INSERT INTO dbo.user_account (account, pay_stat)
        VALUES (@account, 1);

        INSERT INTO dbo.ssn
            (ssn, name, email, job, phone, zip, addr_main, addr_etc, account_num)
        VALUES
            (@ssn, @account, @email, '0', 'telphone', '123456', '', '', 1);

        INSERT INTO dbo.user_auth
            (account, password, quiz1, quiz2, answer1, answer2, new_pwd_flag, otp_flag)
        VALUES
            (@account, @pwdcr, 'sgegxefeer', 'sadvegfvve',
             @answer1cr, @answer2cr, @pwdFlag, @otpflag);

        INSERT INTO dbo.user_info (account, ssn, kind, code)
        VALUES (@account, @ssn, 99, '123');

        SELECT @uid = uid
        FROM dbo.user_account
        WHERE account = @account;

        INSERT INTO dbo.web_account
            (uid, account, passwords, new_pwd_flag, email, password2)
        VALUES
            (@uid, @account, @pwdcr, @otpflag, @email, @pwd);
    END;

    SET NOCOUNT OFF;
END;
GO
