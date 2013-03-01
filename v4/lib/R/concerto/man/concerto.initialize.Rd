\name{concerto.initialize}
\alias{concerto.initialize}
\title{
Concerto test session.
}
\description{
Initializes Concerto test session.
}
\usage{
concerto.initialize(testID, sessionID, workspaceID, workspacePrefix, user, password, dbName, host = "localhost", port = 3306, tempPath, mediaPath, dbTimezone, dbConnect, userIP)
}
\arguments{
  \item{testID}{
Concerto test ID.
}
  \item{sessionID}{
Concerto session ID.
}
  \item{workspaceID}{
Test workspace ID.
}
  \item{workspacePrefix}{
Concerto workspace prefix.
}
  \item{user}{
MySQL database user name.
}
  \item{password}{
MySQL database user password.
}
  \item{dbName}{
MySQL database name.
}
  \item{host}{
MySQL database host.
}
  \item{port}{
MySQL database port.
}
  \item{tempPath}{
Concerto temporary directory path. It's test session working directory.
}
  \item{mediaPath}{
Test owner media path.
}
  \item{dbTimezone}{
MySQL database timezone.
}
  \item{dbConnect}{
Logical: connect to database ?
}
  \item{userIP}{
Test participant IP address.
}
}
\details{
}
\value{
}
\references{
}
\author{
Przemyslaw Lis
}
\note{
}

\seealso{
}
\examples{
#
}
\keyword{ session }
