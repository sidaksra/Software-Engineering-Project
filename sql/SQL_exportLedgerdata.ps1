# ver 1.2



# Define connection details
$server = "servername"
$database = "dbname"
$username = "username"
$password = "password"
$outputPath = "C:\locationhere\SQL_exportLedgerdata_$timestamp.csv"

# Define SQL query
$query = @"
SELECT 
Date,
Pr,
Total 
FROM [ClearDent].[dbo].[Vw_Ledger] 
WHERE Pr IS NOT NULL
/* AND Date <= GETDATE() AND Date > DateAdd(DD,-7,GETDATE() ) */
"@

# Create a SqlConnection object
$connectionString = "Server=$server;Database=$database;User ID=$username;Password=$password;"
$connection = New-Object System.Data.SqlClient.SqlConnection($connectionString)

# Open the database connection
$connection.Open()

# Create a SqlCommand object to execute the SQL query
$command = New-Object System.Data.SqlClient.SqlCommand($query, $connection)

# Execute the SQL query and store the results in a DataTable object
$results = New-Object System.Data.DataTable
$results.Load($command.ExecuteReader())

# Close the database connection
$connection.Close()

# Timetamp for filename
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"

# Export the query results to the CSV file
$results | Export-Csv -Path $outputPath -NoTypeInformation

# Display a message indicating where the file was saved
Write-Host "Query results saved to: $outputPath"