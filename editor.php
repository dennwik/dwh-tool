<pre contenteditable="true" id="myInput"><code>
<span style="color: lightslategrey">CREATE PROCEDURE</span> [Dimension].[Load_<span class="insertDimensionName" style="color: darkseagreen; font-weight: bold">dhPlaceholder</span>]
    @ExecLogSID BIGINT = NULL,
    @Transferred BIGINT = 0 OUT
AS
BEGIN
    SET NOCOUNT ON;
    <span style="color: lightslategrey">BEGIN TRY</span>

        -- DUMMY UPDATE
        UPDATE Dimension.<span class="insertDimensionName" style="color: darkseagreen; font-weight: bold">dhPlaceholder</span>
        <span style="color: lightslategrey">SET</span> <span class="insertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey</span> = <span class="insertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey</span>
        WHERE 1 = 0;

        DECLARE @NASID BIGINT;
        DECLARE @NACHAR NVARCHAR(10);
        SELECT TOP 1
            @NASID = SpecialKeySID,
            @NACHAR = SpecialKeyKey
        FROM Staging.dbo.xSpecialKey
        WHERE SpecialKeyKey = 'N/A';

        DECLARE @SAPMDT NVARCHAR(3);
        SELECT TOP 1
            @SAPMDT = SAPMandantKey
        FROM Dimension.dSAPMandant
        WHERE isActiveSAPMandant = 1;

        DECLARE @rowStartDate DATETIME2(0) = GETDATE(),
        @minRowStartDate DATETIME2(0) =
        (
            SELECT TOP (1) TimeDate FROM Dimension.dTime ORDER BY TimeSID
        ),
        @maxRowEndDate DATETIME2(0) =
        (
            SELECT TOP (1) TimeDate FROM Dimension.dTime ORDER BY TimeSID DESC
        );
        DECLARE @rowEndDate DATETIME2(0) = DATEADD(SECOND, -1, @rowStartDate);

        <span style="color: lightslategrey">SET IDENTITY_INSERT</span> Dimension.<span class="insertDimensionName" style="color: darkseagreen; font-weight: bold">dhPlaceholder</span> <span style="color: lightslategrey">ON</span>;

        <span style="color: lightslategrey">MERGE</span> Dimension.<span class="insertDimensionName" style="color: darkseagreen; font-weight: bold">dhPlaceholder</span> AS dst
        USING
        (
            SELECT
            <span style="color: darkseagreen; font-weight: bold">SpecialKeySID,
            SpecialKeyKey,
            SpecialKeyNameEN,
            SpecialKeyNameDE,</span>
            <span style="color: yellow; font-weight: bold">HASHBYTES</span>('SHA2_256', (SELECT <span class="" style="color: beige; font-weight: bold">SpecialKeyNameEN ,SpecialKeyNameDE</span> FOR JSON PATH))
            AS <span style="color: green; font-weight: bold">RowHash</span>
            FROM Staging.dbo.xSpecialKey
            WHERE SpecialKeyDimension = 1
        ) AS src

        ON src.SpecialKeySID = dst.<span class="insertMainSIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderSID</span>

        -- NOT MATCHED
        <span style="color: lightslategrey">WHEN NOT MATCHED THEN</span>
        <span style="color: lightslategrey">INSERT</span>
        (
            <span class="insertRIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderRID</span>,
            <span class="insertMainSIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderSID</span>,
            <span class="insertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey,</span>,
            <span class="insertColumnName" style="color: darkseagreen; font-weight: bold">
            dhPlaceholderKey,
            dhPlaceholderNameEN,
            dhPlaceholderNameDE,</span>
            RowHash,
            RowIsCurrent,
            RowStartDate,
            RowEndDate
        )
        <span style="color: lightslategrey">VALUES</span>
        (
            <span style="color: darkseagreen; font-weight: bold">src.SpecialKeySID</span>,
            <span class="srcInsertMainSIDSpecialKey" style="color: darkseagreen; font-weight: bold">src.PlaceholderSpecialKeySID,</span>
            <span class="srcInsertMainKeySpecialKey" style="color: darkseagreen; font-weight: bold">src.PlaceholderSpecialKeyKey,</span>
            <span class="srcInsertOtherSpecialKeys" style="color: darkseagreen; font-weight: bold">src.SpecialKeyNameEN,
            src.SpecialKeyNameDE,</span>
            src.RowHash,
            1,
            @minRowStartDate,
            @maxRowEndDate
        )

        -- MATCHED
        <span style="color: lightslategrey">WHEN MATCHED</span> AND src.RowHash <> dst.RowHash THEN
        <span style="color: lightslategrey">UPDATE SET</span>
            <span class="insertFirstKeySchema" style="color: green; font-weight: bold">PlaceholderNameEN = src.SpecialKeyNameEN,</span>
            RowHash = src.RowHash;

        <span style="color: lightslategrey">SET IDENTITY_INSERT</span> Dimension.<span class="insertDimensionName" style="color: darkseagreen; font-weight: bold">dhPlaceholder</span> <span style="color: lightslategrey">OFF</span>;

        <span style="color: lightslategrey">DROP TABLE IF EXISTS #output;</span>
        SELECT TOP (0)
            CAST(0 AS SMALLINT) AS ActionType,
            <span class="insertMainSIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderSID</span> AS <span class="insertRIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderRID</span>,
            <span class="insertMainSIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderSID</span>,
            <span class="insertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey,</span>,
            <span class="insertColumnName" style="color: darkseagreen; font-weight: bold">
            dhPlaceholderNameEN,
            dhPlaceholderNameDE,</span>
            RowHash,
            RowIsCurrent,
            RowStartDate,
            RowEndDate
        <span style="color: lightslategrey">INTO #output</span>
        FROM Dimension.<span class="insertDimensionName" style="color: darkseagreen; font-weight: bold">dhPlaceholder</span>;

        <span style="color: lightslategrey">WITH cte</span>
        AS (
            <span class="CTEcode" style="color: darkseagreen; font-weight: bold">CTE Code</span>
        )

        <span style="color: lightslategrey">MERGE</span> Dimension.<span class="insertDimensionName" style="color: darkseagreen; font-weight: bold">dhPlaceholder</span> AS dst
        USING
        (
            SELECT
                <span class="insertSecondKeySchema" style="color: green; font-weight: bold">PlaceholderKey = c.Placeholder
                PlaceholderNameEN = c.SpecialKeyNameEN,
                PlaceholderNameDE = c.SpecialKeyNameDE,</span>
                <span style="color: yellow; font-weight: bold">RowHash = HASHBYTES</span>
                (
                    'SHA2_256', (SELECT <span class="insertHash" style="color: beige; font-weight: bold">SpecialKeyNameEN ,SpecialKeyNameDE</span> FOR JSON PATH)
                ),
                1 AS RowIsCurrent,
                @rowStartDate AS RowStartDate,
                @maxRowEndDate AS RowEndDate
            FROM cte c
        ) <span style="color: lightslategrey">AS src</span>

        ON
        (
            dst.<span class="insertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey</span> = src.<span class="insertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey</span>
            AND dst.RowIsCurrent = 1
        )

        <span style="color: lightslategrey">WHEN MATCHED AND src.RowHash <> dst.RowHash THEN</span>
        UPDATE SET
            -- Set Current Flag to 0 and RowEndDate to Yesterday or whatever past time or date
            dst.RowIsCurrent = 0,
            dst.RowEndDate = @rowEndDate
        <span style="color: lightslategrey">WHEN NOT MATCHED THEN</span>
        INSERT
        (
            <span class="insertMainSIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderSID</span>,
            <span class="insertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey,</span>,
            <span class="insertColumnName" style="color: darkseagreen; font-weight: bold">
            dhPlaceholderNameEN,
            dhPlaceholderNameDE,</span>
            RowHash,
            RowIsCurrent,
            RowStartDate,
            RowEndDate
        )

        <span style="color: lightslategrey">VALUES</span>
        (
            0,
            <span class="srcInsertMainKeyColumn" style="color: darkseagreen; font-weight: bold">src.PlaceholderKey</span>,
            <span class="srcInsertColumnName" style="color: darkseagreen; font-weight: bold">src.PlaceholderNameEN,
            src.PlaceholderNameEN,</span>
            src.RowHash,
            src.RowIsCurrent,
            src.RowStartDate,
            src.RowEndDate
        )

        <span style="color: lightslategrey">OUTPUT</span>
            -- output rows from source input that will be current versions of updated rows and move them to insert
            Inserted.RowIsCurrent, -- 1=INSERTed, 0=UPDATEd, -1=DELETEd
            Inserted.<span class="insertRIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderRID</span>,
            Inserted.<span class="insertMainSIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderSID</span>,
            <span class="srcInsertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey</span>,
            <span class="srcInsertColumnName" style="color: darkseagreen; font-weight: bold">src.PlaceholderNameEN,
            src.PlaceholderNameDE,</span>
            src.RowHash,
            src.RowIsCurrent,
            src.RowStartDate,
            src.RowEndDate
        INTO #output;

        <span style="color: lightslategrey">SET @Transferred += @@ROWCOUNT;</span>

        UPDATE d
            SET <span class="insertMainSIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderSID</span> = o.<span class="insertRIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderRID</span>
            FROM Dimension.<span class="insertDimensionName" style="color: darkseagreen; font-weight: bold">dhPlaceholder</span> d
                INNER JOIN #output o
                ON o.<span class="insertRIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderRID</span> = d.<span class="insertRIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderRID</span>
            WHERE o.ActionType = 1; -- INSERTED

        INSERT INTO Dimension.<span class="insertDimensionName" style="color: darkseagreen; font-weight: bold">dhPlaceholder</span>
        (
            <span class="insertMainSIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderSID</span>,
            <span class="insertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey</span>,
            <span class="insertColumnName" style="color: darkseagreen; font-weight: bold">dhPlaceholderNameEN,
            dhPlaceholderNameDE,</span>
            RowHash,
            RowIsCurrent,
            RowStartDate,
            RowEndDate
        )
        SELECT
            <span class="insertMainSIDColumn" style="color: darkseagreen; font-weight: bold">PlaceholderSID</span>,
            <span class="insertMainKeyColumn" style="color: darkseagreen; font-weight: bold">PlaceholderKey</span>,
            <span class="insertColumnName" style="color: darkseagreen; font-weight: bold">dhPlaceholderNameEN,
            dhPlaceholderNameDE,</span>
            RowHash,
            RowIsCurrent,
            RowStartDate,
            RowEndDate
        FROM #output
        WHERE ActionType = 0;

        DROP TABLE IF EXISTS #output;

        END TRY
        BEGIN CATCH
            THROW;
        END CATCH;
    END;

</code></pre>