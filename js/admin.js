// Simple Utility module
const UtilityModule = (() => {
    const generateRandomCode = () => {
      const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789"
      let out = ""
      for (let i = 0; i < 8; i++) {
        out += chars[Math.floor(Math.random() * chars.length)]
      }
      return out
    }
  
    const formatDate = (d = new Date()) => {
      const y = d.getFullYear()
      const m = String(d.getMonth() + 1).padStart(2, "0")
      const day = String(d.getDate()).padStart(2, "0")
      return `${y}-${m}-${day}`
    }
  
    return { generateRandomCode, formatDate }
  })()
  
  // Simple State with localStorage persistence
  const StateModule = (() => {
    const KEY = "disc_admin_state_v1"
    let state = {
      adminLoggedIn: false,
      branding: {
        companyName: "CHROMIUM RESOURCES",
        tagline: "Professional Leadership Development Solutions",
        logoUrl: "",
        logoText: "LOGO",
        backgroundUrl: "",
        primaryColor: "#2c3e50",
        secondaryColor: "#3498db",
        companyNameFontSize: "24",
        taglineFontSize: "16",
        companyInfo: "",
        fontFamily: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
      },
      reportSettings: {
        title: "DISC LEADERSHIP ASSESSMENT",
        subtitle: "Professional Development Report",
        logoSize: "medium",
        backgroundOpacity: "0.6",
        backgroundImageSize: "cover",
        backgroundPosition: "center",
        footerText: "Confidential Leadership Development Report",
        pdfBackgroundColor: "#f8f9fa",
        pageHeaderColor: "#3498db",
      },
      accessCodes: [],
      participants: [], // You can populate this to preview participants
    }
  
    const getState = () => state
  
    const setState = (partial) => {
      state = { ...state, ...partial }
      saveToStorage()
    }
  
    const saveToStorage = () => {
      try {
        localStorage.setItem(KEY, JSON.stringify(state))
      } catch (e) {
        // ignore quota/security
      }
    }
  
    const loadFromStorage = () => {
      try {
        const raw = localStorage.getItem(KEY)
        if (raw) state = { ...state, ...JSON.parse(raw) }
      } catch (e) {
        // ignore parse issues
      }
    }
  
    return { getState, setState, saveToStorage, loadFromStorage }
  })()
  
  // Soft stubs for report generation
  const ReportModule = (() => {
    const generatePDF = (participant) => {
      alert(`Report generation would run for: ${participant?.name || "Unknown"}`)
    }
    const generateTeamPDF = (participants) => {
      alert(`Team report for ${participants.length} participant(s).`)
    }
    return { generatePDF, generateTeamPDF }
  })()
  
  // Admin Module
  const AdminModule = (() => {
    let isLoggedIn = false;
  
    const login = async () => {
      const username = prompt("Admin Username:")
      const password = prompt("Admin Password:")
      
      if (!username || !password) {
        alert("Username and password are required")
        return
      }

      try {
        const response = await fetch('api/auth.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            action: 'admin_login',
            username: username,
            password: password
          })
        });

        const result = await response.json();

        if (result.success) {
          isLoggedIn = true
          StateModule.setState({ adminLoggedIn: true })
          updateInputsFromState()
          loadDashboardData()
          applyBranding()
          alert("Admin logged in successfully.")
        } else {
          alert("Invalid admin credentials: " + (result.error || "Login failed"))
        }
      } catch (error) {
        console.error('Login error:', error)
        alert("Login failed: " + error.message)
      }
    }
  
    const uploadLogo = () => {
      const fileInput = document.getElementById("logoUpload")
      const file = fileInput?.files?.[0]
      if (!file) return
  
      const reader = new FileReader()
      reader.onload = (e) => {
        const branding = { ...StateModule.getState().branding }
        branding.logoUrl = e.target.result
        StateModule.setState({ branding })
        applyBranding()
      }
      reader.readAsDataURL(file)
    }
  
    const uploadBackground = () => {
      const fileInput = document.getElementById("backgroundUpload")
      const file = fileInput?.files?.[0]
      if (!file) return
  
      const reader = new FileReader()
      reader.onload = (e) => {
        const branding = { ...StateModule.getState().branding }
        branding.backgroundUrl = e.target.result
        StateModule.setState({ branding })
        applyBranding()
      }
      reader.readAsDataURL(file)
    }
  
    const generateAccessCodes = async () => {
      const count = prompt("How many access codes to generate? (1-100)", "20")
      const numCodes = parseInt(count)
      
      if (!numCodes || numCodes < 1 || numCodes > 100) {
        alert("Please enter a number between 1 and 100")
        return
      }

      try {
        const response = await fetch('api/admin.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            action: 'generate_codes',
            count: numCodes
          })
        });

        const result = await response.json();

        if (result.success) {
          alert(`Successfully generated ${numCodes} access codes`)
          loadAccessCodes()
        } else {
          alert("Error generating codes: " + (result.error || "Unknown error"))
        }
      } catch (error) {
        console.error('Generate codes error:', error)
        alert("Error generating codes: " + error.message)
      }
    }
  
    const loadAccessCodes = async () => {
      try {
        const response = await fetch('api/admin.php?action=access_codes')
        const result = await response.json()

        if (result.success) {
          displayAccessCodes(result.data.access_codes)
        } else {
          console.error('Error loading access codes:', result.error)
        }
      } catch (error) {
        console.error('Error loading access codes:', error)
      }
    }

    const displayAccessCodes = (accessCodes = []) => {
      const container = document.getElementById("accessCodesContainer")
      const countElement = document.getElementById("codeCount")
      if (!container || !countElement) return

      container.innerHTML = ""
      
      const usedCodes = accessCodes.filter(code => code.is_used).length
      const availableCodes = accessCodes.length - usedCodes
      
      countElement.textContent = `${accessCodes.length} total codes (${availableCodes} available, ${usedCodes} used)`

      accessCodes.forEach((code) => {
        const codeDiv = document.createElement("div")
        codeDiv.className = `access-code-item ${code.is_used ? 'used' : 'available'}`
        codeDiv.innerHTML = `
          <span class="code">${code.code}</span>
          ${code.is_used ? `<span class="used-by">Used by: ${code.used_by || 'Unknown'}</span>` : '<span class="status">Available</span>'}
        `
        container.appendChild(codeDiv)
      })
    }
  
    const loadParticipants = async () => {
      try {
        const response = await fetch('api/admin.php?action=participants')
        const result = await response.json()

        if (result.success) {
          displayParticipants(result.data.participants)
        } else {
          console.error('Error loading participants:', result.error)
        }
      } catch (error) {
        console.error('Error loading participants:', error)
      }
    }

    const displayParticipants = (participants = []) => {
      const tbody = document.getElementById("participantsTableBody")
      if (!tbody) return

      if (!participants.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty">No completed assessments yet</td></tr>'
        updateParticipantSelection(participants)
        return
      }

      tbody.innerHTML = participants
        .map(
          (p) => `
        <tr>
          <td>${p.full_name}</td>
          <td>${p.position || '—'}</td>
          <td>${p.completed_at ? new Date(p.completed_at).toLocaleDateString() : 'Incomplete'}</td>
          <td>${p.profile_title || "—"}</td>
          <td>
            <button class="btn btn-secondary" onclick="AdminModule.generateReport('${p.id}')" style="margin-right:6px;">Report</button>
            <button class="btn" onclick="AdminModule.deleteParticipant('${p.id}')" style="background: var(--accent-color);">Delete</button>
          </td>
        </tr>
      `,
        )
        .join("")

      updateParticipantSelection(participants)
    }
  
    const updateParticipantSelection = (participants = []) => {
      const container = document.getElementById("participantSelectionContainer")
      const selectedCount = document.getElementById("selectedCount")
      const teamReportBtn = document.getElementById("teamReportBtn")
      if (!container || !selectedCount || !teamReportBtn) return

      if (!participants.length) {
        container.innerHTML = '<p class="empty" style="margin:0;">No participants available for selection</p>'
        selectedCount.textContent = "0 participants selected (minimum 3 required)"
        teamReportBtn.disabled = true
        return
      }

      container.innerHTML = participants
        .filter(p => p.assessment_completed) // Only show completed assessments
        .map(
          (p) => `
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px; padding:8px; border-radius:4px; background:#f8f9fa;">
          <input type="checkbox"
             id="participant_${p.id}"
             value="${p.id}"
             onchange="AdminModule.updateSelectedCount()"
             style="transform:scale(1.2);" />
          <label for="participant_${p.id}" style="flex:1; cursor:pointer; margin:0;">
            <strong>${p.full_name}</strong> - ${p.position || 'Unknown'} (${p.profile_title || "Profile"})
          </label>
        </div>
      `,
        )
        .join("")

      updateSelectedCount()
    }
  
    const updateSelectedCount = () => {
      const checkboxes = document.querySelectorAll('#participantSelectionContainer input[type="checkbox"]')
      const selectedCount = document.getElementById("selectedCount")
      const teamReportBtn = document.getElementById("teamReportBtn")
      if (!selectedCount || !teamReportBtn) return
  
      const selected = Array.from(checkboxes).filter((cb) => cb.checked)
      const count = selected.length
      selectedCount.textContent = `${count} participants selected (minimum 3 required)`
      teamReportBtn.disabled = count < 3
      selectedCount.style.color = count >= 3 ? "#27ae60" : "#0c5460"
    }
  
    const selectAllParticipants = () => {
      document
        .querySelectorAll('#participantSelectionContainer input[type="checkbox"]')
        .forEach((cb) => (cb.checked = true))
      updateSelectedCount()
    }
  
    const clearAllParticipants = () => {
      document
        .querySelectorAll('#participantSelectionContainer input[type="checkbox"]')
        .forEach((cb) => (cb.checked = false))
      updateSelectedCount()
    }
  
    const generateSelectedTeamReport = () => {
      const { participants } = StateModule.getState()
      const checked = document.querySelectorAll('#participantSelectionContainer input[type="checkbox"]:checked')
      if (checked.length < 3) {
        alert("Please select at least 3 participants for the team report.")
        return
      }
      const ids = Array.from(checked).map((cb) => cb.value)
      const selected = participants.filter((p) => ids.includes(p.id))
      ReportModule.generateTeamPDF(selected)
    }
  
    const generateReport = (participantId) => {
      const { participants } = StateModule.getState()
      const participant = participants.find((p) => p.id === participantId)
      if (!participant) {
        alert("Participant not found")
        return
      }
      ReportModule.generatePDF(participant)
    }
  
    const deleteParticipant = async (participantId) => {
      if (!confirm("Delete this participant and their assessment data? This cannot be undone.")) {
        return
      }

      try {
        const response = await fetch('api/admin.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            action: 'delete_participant',
            participant_id: participantId
          })
        });

        const result = await response.json();

        if (result.success) {
          alert("Participant data deleted successfully.")
          loadParticipants()
        } else {
          alert("Error deleting participant: " + (result.error || "Unknown error"))
        }
      } catch (error) {
        console.error('Delete participant error:', error)
        alert("Error deleting participant: " + error.message)
      }
    }
  
    const updateBranding = () => {
      const branding = {
        ...StateModule.getState().branding,
        companyName: document.getElementById("adminCompanyName").value,
        tagline: document.getElementById("adminTagline").value,
        companyInfo: document.getElementById("companyInfo").value,
        companyNameFontSize: document.getElementById("companyNameFontSize").value,
        taglineFontSize: document.getElementById("taglineFontSize").value,
      }
      StateModule.setState({ branding })
      applyBranding()
    }
  
    const updateColors = () => {
      const branding = {
        ...StateModule.getState().branding,
        primaryColor: document.getElementById("primaryColor").value,
        secondaryColor: document.getElementById("secondaryColor").value,
      }
      StateModule.setState({ branding })
      applyBranding()
    }
  
    const updateFont = () => {
      const branding = {
        ...StateModule.getState().branding,
        fontFamily: document.getElementById("fontFamily").value,
      }
      StateModule.setState({ branding })
      applyBranding()
    }
  
    const updateReportSettings = () => {
      const rs = {
        ...StateModule.getState().reportSettings,
        title: document.getElementById("reportTitle").value,
        subtitle: document.getElementById("reportSubtitle").value,
        logoSize: document.getElementById("logoSize").value,
        backgroundOpacity: document.getElementById("backgroundOpacity").value,
        backgroundImageSize: document.getElementById("backgroundImageSize").value,
        backgroundPosition: document.getElementById("backgroundPosition").value,
        footerText: document.getElementById("reportFooter").value,
        pdfBackgroundColor: document.getElementById("pdfBackgroundColor").value,
        pageHeaderColor: document.getElementById("pageHeaderColor").value,
      }
      StateModule.setState({ reportSettings: rs })
    }
  
    const updateInputsFromState = () => {
      const { branding, reportSettings } = StateModule.getState()
      // Branding inputs
      document.getElementById("adminCompanyName").value = branding.companyName
      document.getElementById("adminTagline").value = branding.tagline
      document.getElementById("companyInfo").value = branding.companyInfo || ""
      document.getElementById("primaryColor").value = branding.primaryColor
      document.getElementById("secondaryColor").value = branding.secondaryColor
      document.getElementById("fontFamily").value = branding.fontFamily
      document.getElementById("companyNameFontSize").value = branding.companyNameFontSize || "24"
      document.getElementById("taglineFontSize").value = branding.taglineFontSize || "16"
      // Report settings inputs
      document.getElementById("reportTitle").value = reportSettings.title
      document.getElementById("reportSubtitle").value = reportSettings.subtitle
      document.getElementById("logoSize").value = reportSettings.logoSize
      document.getElementById("backgroundOpacity").value = reportSettings.backgroundOpacity
      document.getElementById("backgroundImageSize").value = reportSettings.backgroundImageSize || "cover"
      document.getElementById("backgroundPosition").value = reportSettings.backgroundPosition || "center"
      document.getElementById("reportFooter").value = reportSettings.footerText
      document.getElementById("pdfBackgroundColor").value = reportSettings.pdfBackgroundColor || "#f8f9fa"
      document.getElementById("pageHeaderColor").value = reportSettings.pageHeaderColor || "#3498db"
    }
  
    const saveAllSettings = () => {
      // Apply all updates once
      updateBranding()
      updateColors()
      updateFont()
      updateReportSettings()
  
      const saveStatus = document.getElementById("saveStatus")
      if (saveStatus) {
        saveStatus.textContent = "✅ Settings Saved Successfully!"
        saveStatus.style.color = "var(--success-color)"
        setTimeout(() => (saveStatus.textContent = ""), 2500)
      }
    }
  
    const applyBranding = () => {
      const { branding } = StateModule.getState()
  
      // Update text content and font sizes
      const companyNameElement = document.getElementById("companyName")
      const taglineElement = document.getElementById("companyTagline")
      if (companyNameElement) {
        companyNameElement.textContent = branding.companyName
        companyNameElement.style.fontSize = branding.companyNameFontSize + "px"
      }
      if (taglineElement) {
        taglineElement.textContent = branding.tagline
        taglineElement.style.fontSize = branding.taglineFontSize + "px"
      }
  
      // Update logo
      const logoElement = document.getElementById("companyLogo")
      if (logoElement) {
        if (branding.logoUrl) {
          logoElement.style.backgroundImage = `url(${branding.logoUrl})`
          logoElement.style.backgroundSize = "cover"
          logoElement.style.backgroundPosition = "center"
          logoElement.textContent = ""
        } else {
          logoElement.style.backgroundImage = "none"
          logoElement.textContent = branding.logoText
        }
      }
  
      // Update header background if any
      const headerElement = document.getElementById("headerSection")
      if (headerElement && branding.backgroundUrl) {
        headerElement.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url(${branding.backgroundUrl})`
        headerElement.style.backgroundSize = "cover"
        headerElement.style.backgroundPosition = "center"
      }
  
      // Update global CSS variables
      document.documentElement.style.setProperty("--primary-color", branding.primaryColor)
      document.documentElement.style.setProperty("--secondary-color", branding.secondaryColor)
      document.documentElement.style.setProperty("--custom-font", branding.fontFamily)
    }
  
    const loadDashboardData = async () => {
      try {
        const response = await fetch('api/admin.php?action=dashboard_stats')
        const result = await response.json()

        if (result.success) {
          updateDashboardStats(result.data)
        } else {
          console.error('Error loading dashboard stats:', result.error)
        }
      } catch (error) {
        console.error('Error loading dashboard stats:', error)
      }
    }

    const updateDashboardStats = (stats) => {
      document.getElementById('totalParticipants').textContent = stats.total_participants || 0
      document.getElementById('totalCodes').textContent = stats.total_codes || 0
      document.getElementById('completedAssessments').textContent = stats.completed_assessments || 0
      document.getElementById('pendingAssessments').textContent = stats.pending_assessments || 0
    }

    return {
      login,
      uploadLogo,
      uploadBackground,
      generateAccessCodes,
      loadAccessCodes,
      displayAccessCodes,
      loadParticipants,
      displayParticipants,
      selectAllParticipants,
      clearAllParticipants,
      updateSelectedCount,
      generateSelectedTeamReport,
      generateReport,
      deleteParticipant,
      updateBranding,
      updateColors,
      updateFont,
      updateReportSettings,
      saveAllSettings,
      applyBranding,
      loadDashboardData,
      updateDashboardStats,
    }
  })()
  
  // Init
  document.addEventListener("DOMContentLoaded", () => {
    StateModule.loadFromStorage()
    AdminModule.applyBranding()
    
    // Load initial data if admin is logged in
    if (typeof window.isLoggedIn !== 'undefined' && window.isLoggedIn) {
      AdminModule.loadDashboardData()
      AdminModule.loadAccessCodes()
      AdminModule.loadParticipants()
    }
    
    // Reflect persisted values in inputs
    ;(function syncInputs() {
      const { branding, reportSettings } = StateModule.getState()
      // Attempt to set inputs if they are in DOM already
      try {
        document.getElementById("adminCompanyName").value = branding.companyName
        document.getElementById("adminTagline").value = branding.tagline
        document.getElementById("companyInfo").value = branding.companyInfo || ""
        document.getElementById("primaryColor").value = branding.primaryColor
        document.getElementById("secondaryColor").value = branding.secondaryColor
        document.getElementById("fontFamily").value = branding.fontFamily
        document.getElementById("companyNameFontSize").value = branding.companyNameFontSize || "24"
        document.getElementById("taglineFontSize").value = branding.taglineFontSize || "16"

        document.getElementById("reportTitle").value = reportSettings.title
        document.getElementById("reportSubtitle").value = reportSettings.subtitle
        document.getElementById("logoSize").value = reportSettings.logoSize
        document.getElementById("backgroundOpacity").value = reportSettings.backgroundOpacity
        document.getElementById("backgroundImageSize").value = reportSettings.backgroundImageSize || "cover"
        document.getElementById("backgroundPosition").value = reportSettings.backgroundPosition || "center"
        document.getElementById("reportFooter").value = reportSettings.footerText
        document.getElementById("pdfBackgroundColor").value = reportSettings.pdfBackgroundColor || "#f8f9fa"
        document.getElementById("pageHeaderColor").value = reportSettings.pageHeaderColor || "#3498db"
      } catch (e) {
        // noop
      }
    })()
  })
  
  // Expose for onclick handlers
  window.AdminModule = AdminModule
  window.ReportModule = ReportModule
  