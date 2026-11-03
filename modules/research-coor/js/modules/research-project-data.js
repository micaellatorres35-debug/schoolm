const RESEARCH_PROJECT_API = new URL("../../api/research_projects.php", import.meta.url);

async function readApiResponse(response) {
  let payload;
  try {
    payload = await response.json();
  } catch (error) {
    throw new Error("Hindi mabasa ang sagot ng research project server.");
  }
  if (!response.ok || payload.success !== true) {
    throw new Error(payload.message || "Hindi makumpleto ang research project request.");
  }
  return payload;
}

export async function fetchResearchProjectData() {
  return readApiResponse(await fetch(RESEARCH_PROJECT_API, {
    headers: { Accept: "application/json" },
    credentials: "same-origin",
  }));
}

export async function fetchResearchProjects() {
  const { projects } = await fetchResearchProjectData();
  return projects;
}

export async function saveResearchProject(project) {
  const isUpdate = Number.isInteger(Number(project.id)) && Number(project.id) > 0;
  return readApiResponse(await fetch(RESEARCH_PROJECT_API, {
    method: isUpdate ? "PUT" : "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    credentials: "same-origin",
    body: JSON.stringify(project),
  }));
}
