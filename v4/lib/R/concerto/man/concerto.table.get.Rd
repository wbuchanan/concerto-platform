\name{concerto.table.get}
\alias{concerto.table.get}
\title{
Table object retrieval
}
\description{
Gets table object.
}
\usage{
concerto.table.get(tableID, workspaceID = concerto$workspaceID)
}
\arguments{
  \item{tableID}{
Table object ID
}
  \item{workspaceID}{
Concerto workspace ID where the table object is located.
}
}
\details{
}
\value{
List with table object properties:
\item{id }{table object ID}
\item{name}{table object name}
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
table <- concerto.table.get(1)
}

\keyword{ table }
\keyword{ MySQL }
\keyword{ database }
