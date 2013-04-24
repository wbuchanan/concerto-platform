\name{concerto.template.get}
\alias{concerto.template.get}
\title{
Template object retrieval
}
\description{
Gets template object.
}
\usage{
concerto.template.get(templateID, workspaceID = concerto$workspaceID)
}
\arguments{
  \item{templateID}{
Template ID or name.
}
  \item{workspaceID}{
Concerto workspace ID where template is located.
}
}
\details{
}
\value{
List with template object properties:
\item{id }{template object ID}
\item{name}{template object name}
\item{head}{head tag contents}
\item{HTML}{template HTML}
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
template1 <- concerto.template.get(1)
template2 <- concerto.template.get("some_other_template")
}

\keyword{ template }
\keyword{ HTML }
