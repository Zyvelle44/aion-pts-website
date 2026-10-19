
USE [AionAccounts];
GO

-- Create web_account only when it does not already exist.
-- Existing rows and schema will not be dropped or overwritten.
IF OBJECT_ID(N'dbo.web_account', N'U') IS NULL
BEGIN
    CREATE TABLE [dbo].[web_account] (
        [uid] BIGINT NULL,
        [account] VARCHAR(14) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
        [passwords] BINARY(16) NULL,
        [new_pwd_flag] TINYINT NULL,
        [email] VARCHAR(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
        [password2] NVARCHAR(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
        [row_id] BIGINT IDENTITY(1,1) NOT NULL,
        [create_date] DATETIME2(7) NULL
            CONSTRAINT [DF_web_account_create_date] DEFAULT GETDATE(),
        [donate_coin] INT NULL
            CONSTRAINT [DF_web_account_donate_coin] DEFAULT 0,
        [login_flag_web] TINYINT NOT NULL
            CONSTRAINT [DF_web_account_login_flag_web] DEFAULT 0,

        CONSTRAINT [PK_web_account_row_id]
            PRIMARY KEY CLUSTERED ([row_id])
    );

    ALTER TABLE [dbo].[web_account]
        SET (LOCK_ESCALATION = TABLE);

    PRINT 'dbo.web_account created successfully.';
END
ELSE
BEGIN
    PRINT 'dbo.web_account already exists. No changes made.';
END;
GO
