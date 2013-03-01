\name{concerto.test.getReturnVariables}
\alias{concerto.test.getReturnVariables}
\title{
Test return variables retrieval
}
\description{
Gets test return variables.
}
\usage{
concerto.test.getReturnVariables(testID, workspaceID = concerto$workspaceID)
}
\arguments{
  \item{testID}{
Test ID.
}
  \item{workspaceID}{
Concerto workspace ID where test object is located.
}
}
\details{
}
\value{
Named list with test object return variables.
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
rv <- concerto.test.getReturnVariables(1)
}

\keyword{ test }
